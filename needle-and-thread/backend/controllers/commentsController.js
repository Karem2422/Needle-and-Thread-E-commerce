const db = require('../config/database');
const { validationResult } = require('express-validator');

/**
 * Get comments for a product
 * GET /api/products/:productId/comments
 */
exports.getComments = async (req, res, next) => {
    try {
        const { productId } = req.params;

        // Only show approved comments to non-admin users
        let query = 'SELECT * FROM comments WHERE product_id = ?';
        const params = [productId];

        if (!req.user || !req.user.isAdmin) {
            query += ' AND approved = 1';
        }

        query += ' ORDER BY created_at DESC';

        const [comments] = await db.query(query, params);

        res.json({
            success: true,
            data: comments
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Add comment to product
 * POST /api/products/:productId/comments
 */
exports.addComment = async (req, res, next) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                message: errors.array()[0].msg
            });
        }

        const { productId } = req.params;
        const { name, email, comment } = req.body;

        // Check if product exists
        const [products] = await db.query('SELECT id FROM products WHERE id = ?', [productId]);
        if (products.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Product not found'
            });
        }

        // Auto-approve if admin
        const approved = req.user.isAdmin ? 1 : 0;

        const [result] = await db.query(
            'INSERT INTO comments (product_id, user_id, name, email, comment, approved) VALUES (?, ?, ?, ?, ?, ?)',
            [productId, req.user.userId, name, email, comment, approved]
        );

        res.status(201).json({
            success: true,
            message: approved ? 'Comment added successfully' : 'Comment submitted for approval',
            data: {
                commentId: result.insertId
            }
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Approve comment (Admin only)
 * PUT /api/comments/:id/approve
 */
exports.approveComment = async (req, res, next) => {
    try {
        const { id } = req.params;

        const [result] = await db.query(
            'UPDATE comments SET approved = 1 WHERE id = ?',
            [id]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Comment not found'
            });
        }

        res.json({
            success: true,
            message: 'Comment approved successfully'
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Delete comment (Admin only)
 * DELETE /api/comments/:id
 */
exports.deleteComment = async (req, res, next) => {
    try {
        const { id } = req.params;

        const [result] = await db.query('DELETE FROM comments WHERE id = ?', [id]);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Comment not found'
            });
        }

        res.json({
            success: true,
            message: 'Comment deleted successfully'
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Get all comments (Admin only)
 * GET /api/admin/comments
 */
exports.getAllComments = async (req, res, next) => {
    try {
        const { approved = '', page = 1, limit = 20 } = req.query;
        const offset = (parseInt(page) - 1) * parseInt(limit);

        let query = `
      SELECT c.*, p.title as product_title
      FROM comments c
      LEFT JOIN products p ON c.product_id = p.id
      WHERE 1=1
    `;
        const params = [];

        if (approved !== '') {
            query += ' AND c.approved = ?';
            params.push(parseInt(approved));
        }

        // Get total count
        const countQuery = query.replace(
            'SELECT c.*, p.title as product_title',
            'SELECT COUNT(*) as total'
        );
        const [countResult] = await db.query(countQuery, params);
        const total = countResult[0].total;

        // Add pagination
        query += ' ORDER BY c.created_at DESC LIMIT ? OFFSET ?';
        params.push(parseInt(limit), offset);

        const [comments] = await db.query(query, params);

        res.json({
            success: true,
            data: comments,
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
