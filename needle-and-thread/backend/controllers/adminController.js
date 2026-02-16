const db = require('../config/database');

/**
 * Get dashboard statistics
 * GET /api/admin/stats
 */
exports.getStats = async (req, res, next) => {
    try {
        // Get counts
        const [productCount] = await db.query('SELECT COUNT(*) as count FROM products');
        const [orderCount] = await db.query('SELECT COUNT(*) as count FROM orders');
        const [userCount] = await db.query('SELECT COUNT(*) as count FROM users');

        // Get revenue (exclude cancelled orders)
        const [revenueResult] = await db.query(
            "SELECT SUM(total_price) as revenue FROM orders WHERE status != 'cancelled'"
        );

        // Get recent orders
        const [recentOrders] = await db.query(
            `SELECT o.*, u.name as customer_name
       FROM orders o
       LEFT JOIN users u ON o.user_id = u.id
       ORDER BY o.created_at DESC
       LIMIT 5`
        );

        // Get low stock products
        const [lowStock] = await db.query(
            "SELECT * FROM products WHERE quantity <= 5 AND status = 'available' ORDER BY quantity ASC LIMIT 5"
        );

        res.json({
            success: true,
            data: {
                stats: {
                    products: productCount[0].count,
                    orders: orderCount[0].count,
                    users: userCount[0].count,
                    revenue: revenueResult[0].revenue || 0
                },
                recentOrders,
                lowStock
            }
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Get all users
 * GET /api/admin/users
 */
exports.getUsers = async (req, res, next) => {
    try {
        const { page = 1, limit = 20 } = req.query;
        const offset = (parseInt(page) - 1) * parseInt(limit);

        // Get total count
        const [countResult] = await db.query('SELECT COUNT(*) as total FROM users');
        const total = countResult[0].total;

        // Get users
        const [users] = await db.query(
            `SELECT id, name, email, is_admin, created_at
       FROM users
       ORDER BY created_at DESC
       LIMIT ? OFFSET ?`,
            [parseInt(limit), offset]
        );

        res.json({
            success: true,
            data: users,
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
 * Get inventory logs
 * GET /api/admin/inventory-logs
 */
exports.getInventoryLogs = async (req, res, next) => {
    try {
        const { productId = '', page = 1, limit = 50 } = req.query;
        const offset = (parseInt(page) - 1) * parseInt(limit);

        let query = 'SELECT * FROM inventory_logs WHERE 1=1';
        let countQuery = 'SELECT COUNT(*) as total FROM inventory_logs WHERE 1=1';
        const params = [];

        if (productId) {
            query += ' AND product_id = ?';
            countQuery += ' AND product_id = ?';
            params.push(productId);
        }

        // Get total count
        const [countResult] = await db.query(countQuery, params);
        const total = countResult[0].total;

        // Get logs
        query += ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        params.push(parseInt(limit), offset);

        const [logs] = await db.query(query, params);

        res.json({
            success: true,
            data: logs,
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
 * Toggle user admin status
 * PUT /api/admin/users/:id/toggle-admin
 */
exports.toggleAdminStatus = async (req, res, next) => {
    try {
        const { id } = req.params;

        // Don't allow changing own admin status
        if (parseInt(id) === req.user.userId) {
            return res.status(400).json({
                success: false,
                message: 'Cannot change your own admin status'
            });
        }

        const [result] = await db.query(
            'UPDATE users SET is_admin = NOT is_admin WHERE id = ?',
            [id]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        res.json({
            success: true,
            message: 'Admin status updated successfully'
        });
    } catch (error) {
        next(error);
    }
};
