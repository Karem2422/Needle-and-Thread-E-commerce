const db = require('../config/database');
const { validationResult } = require('express-validator');

/**
 * Get all orders (admin: all, user: own orders)
 * GET /api/orders
 */
exports.getOrders = async (req, res, next) => {
    try {
        const { status = '', page = 1, limit = 20 } = req.query;
        const offset = (parseInt(page) - 1) * parseInt(limit);

        let query, countQuery, params = [];

        if (req.user.isAdmin) {
            // Admin sees all orders
            query = `
        SELECT o.*, u.name as customer_name
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE 1=1
      `;
            countQuery = 'SELECT COUNT(*) as total FROM orders o WHERE 1=1';

            if (status) {
                query += ' AND o.status = ?';
                countQuery += ' AND o.status = ?';
                params.push(status);
            }
        } else {
            // Regular user sees only their orders
            query = 'SELECT * FROM orders WHERE user_id = ?';
            countQuery = 'SELECT COUNT(*) as total FROM orders WHERE user_id = ?';
            params.push(req.user.userId);
        }

        // Get total count
        const [countResult] = await db.query(countQuery, params);
        const total = countResult[0].total;

        // Add pagination
        query += ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        params.push(parseInt(limit), offset);

        const [orders] = await db.query(query, params);

        res.json({
            success: true,
            data: orders,
            pagination: {
                page: parseInt(page),
                limit: parseInt(limit),
                total,
                pages: Math.ceil(total / parseInt(limit))
            }
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Get single order by ID
 * GET /api/orders/:id
 */
exports.getOrderById = async (req, res, next) => {
    try {
        const { id } = req.params;

        const [orders] = await db.query(
            `SELECT o.*, u.name as customer_name
       FROM orders o
       LEFT JOIN users u ON o.user_id = u.id
       WHERE o.id = ?`,
            [id]
        );

        if (orders.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Order not found'
            });
        }

        const order = orders[0];

        // Check permissions
        if (!req.user.isAdmin && order.user_id !== req.user.userId) {
            return res.status(403).json({
                success: false,
                message: 'Access denied'
            });
        }

        // Get order items
        const [items] = await db.query(
            `SELECT oi.*, p.title, p.slug, pi.filename as image
       FROM order_items oi
       LEFT JOIN products p ON oi.product_id = p.id
       LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
       WHERE oi.order_id = ?`,
            [id]
        );

        // Get status history
        const [history] = await db.query(
            `SELECT h.*, u.name as admin_name
       FROM order_status_history h
       LEFT JOIN users u ON h.changed_by = u.id
       WHERE h.order_id = ?
       ORDER BY h.created_at DESC`,
            [id]
        );

        res.json({
            success: true,
            data: {
                ...order,
                items,
                statusHistory: history
            }
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Create new order
 * POST /api/orders
 */
exports.createOrder = async (req, res, next) => {
    const connection = await db.getConnection();

    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                message: errors.array()[0].msg
            });
        }

        const { name, email, address, phone, items, totalPrice, paymentStatus = 'pending' } = req.body;

        if (!items || items.length === 0) {
            return res.status(400).json({
                success: false,
                message: 'Order must contain at least one item'
            });
        }

        await connection.beginTransaction();

        // Create order
        const orderStatus = paymentStatus === 'paid' ? 'paid' : 'pending';
        const [orderResult] = await connection.query(
            'INSERT INTO orders (user_id, name, email, address, total_price, status) VALUES (?, ?, ?, ?, ?, ?)',
            [req.user.userId, name, email, address, parseFloat(totalPrice), orderStatus]
        );

        const orderId = orderResult.insertId;

        // Record initial status
        await connection.query(
            'INSERT INTO order_status_history (order_id, changed_by, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)',
            [orderId, req.user.userId, null, 'pending', 'Order created']
        );

        // Add order items and update inventory
        for (const item of items) {
            const { productId, quantity, price } = item;

            // Check product availability
            const [products] = await connection.query(
                'SELECT quantity, title FROM products WHERE id = ?',
                [productId]
            );

            if (products.length === 0) {
                throw new Error(`Product ID ${productId} not found`);
            }

            if (products[0].quantity < quantity) {
                throw new Error(`Insufficient quantity for ${products[0].title}`);
            }

            // Add order item
            await connection.query(
                'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)',
                [orderId, productId, parseInt(quantity), parseFloat(price)]
            );

            // Update product quantity
            await connection.query(
                'UPDATE products SET quantity = quantity - ? WHERE id = ?',
                [parseInt(quantity), productId]
            );

            // Update product status if needed
            await connection.query(
                `UPDATE products SET status = CASE 
          WHEN quantity <= 0 THEN 'sold_out' 
          ELSE 'available' 
        END WHERE id = ?`,
                [productId]
            );

            // Log inventory change
            await connection.query(
                'INSERT INTO inventory_logs (product_id, product_name, user_id, change_type, quantity_change, note) VALUES (?, ?, ?, ?, ?, ?)',
                [productId, products[0].title, req.user.userId, 'order', -parseInt(quantity), `Order #${orderId} placed`]
            );
        }

        await connection.commit();

        res.status(201).json({
            success: true,
            message: 'Order created successfully',
            data: {
                orderId
            }
        });
    } catch (error) {
        await connection.rollback();
        next(error);
    } finally {
        connection.release();
    }
};

/**
 * Update order status (Admin only)
 * PUT /api/orders/:id/status
 */
exports.updateOrderStatus = async (req, res, next) => {
    const connection = await db.getConnection();

    try {
        const { id } = req.params;
        const { status } = req.body;

        const validStatuses = ['pending', 'paid', 'shipped', 'cancelled'];
        if (!validStatuses.includes(status)) {
            return res.status(400).json({
                success: false,
                message: 'Invalid status'
            });
        }

        await connection.beginTransaction();

        // Get current order
        const [orders] = await connection.query(
            'SELECT * FROM orders WHERE id = ?',
            [id]
        );

        if (orders.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Order not found'
            });
        }

        const order = orders[0];
        const oldStatus = order.status;

        if (oldStatus === status) {
            return res.status(400).json({
                success: false,
                message: `Order is already ${status}`
            });
        }

        // Update order status
        await connection.query(
            'UPDATE orders SET status = ? WHERE id = ?',
            [status, id]
        );

        // Record status history
        await connection.query(
            'INSERT INTO order_status_history (order_id, changed_by, old_status, new_status, note) VALUES (?, ?, ?, ?, ?)',
            [id, req.user.userId, oldStatus, status, 'Status updated by admin']
        );

        // If cancelling, restore inventory
        if (oldStatus !== 'cancelled' && status === 'cancelled') {
            const [items] = await connection.query(
                `SELECT oi.*, p.title
         FROM order_items oi
         LEFT JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id = ?`,
                [id]
            );

            for (const item of items) {
                // Restore quantity
                await connection.query(
                    'UPDATE products SET quantity = quantity + ? WHERE id = ?',
                    [item.quantity, item.product_id]
                );

                // Update status
                await connection.query(
                    `UPDATE products SET status = CASE 
            WHEN quantity > 0 THEN 'available' 
            ELSE 'sold_out' 
          END WHERE id = ?`,
                    [item.product_id]
                );

                // Log inventory change
                await connection.query(
                    'INSERT INTO inventory_logs (product_id, product_name, user_id, change_type, quantity_change, note) VALUES (?, ?, ?, ?, ?, ?)',
                    [item.product_id, item.title, req.user.userId, 'refund', item.quantity, `Order #${id} cancelled - restocked`]
                );
            }
        }

        await connection.commit();

        res.json({
            success: true,
            message: 'Order status updated successfully'
        });
    } catch (error) {
        await connection.rollback();
        next(error);
    } finally {
        connection.release();
    }
};

/**
 * Get order status history
 * GET /api/orders/:id/history
 */
exports.getOrderHistory = async (req, res, next) => {
    try {
        const { id } = req.params;

        // Check if order exists and user has permission
        const [orders] = await db.query('SELECT user_id FROM orders WHERE id = ?', [id]);

        if (orders.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Order not found'
            });
        }

        if (!req.user.isAdmin && orders[0].user_id !== req.user.userId) {
            return res.status(403).json({
                success: false,
                message: 'Access denied'
            });
        }

        const [history] = await db.query(
            `SELECT h.*, u.name as admin_name
       FROM order_status_history h
       LEFT JOIN users u ON h.changed_by = u.id
       WHERE h.order_id = ?
       ORDER BY h.created_at DESC`,
            [id]
        );

        res.json({
            success: true,
            data: history
        });
    } catch (error) {
        next(error);
    }
};
