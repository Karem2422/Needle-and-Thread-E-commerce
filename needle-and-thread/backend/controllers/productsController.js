const db = require('../config/database');
const { validationResult } = require('express-validator');
const multer = require('multer');
const path = require('path');
const fs = require('fs').promises;

/**
 * Get all products with filters and pagination
 * GET /api/products
 */
exports.getProducts = async (req, res, next) => {
    try {
        const {
            search = '',
            minPrice = '',
            maxPrice = '',
            status = '',
            page = 1,
            limit = 9
        } = req.query;

        const offset = (parseInt(page) - 1) * parseInt(limit);

        // Build query
        let query = `
      SELECT p.*, pi.filename as primary_image
      FROM products p
      LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
      WHERE 1=1
    `;
        const params = [];

        if (search) {
            query += ' AND (p.title LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)';
            const searchTerm = `%${search}%`;
            params.push(searchTerm, searchTerm, searchTerm);
        }

        if (minPrice) {
            query += ' AND p.price >= ?';
            params.push(parseFloat(minPrice));
        }

        if (maxPrice) {
            query += ' AND p.price <= ?';
            params.push(parseFloat(maxPrice));
        }

        if (status) {
            query += ' AND p.status = ?';
            params.push(status);
        }

        // Get total count
        const countQuery = query.replace(
            'SELECT p.*, pi.filename as primary_image',
            'SELECT COUNT(DISTINCT p.id) as total'
        );
        const [countResult] = await db.query(countQuery, params);
        const total = countResult[0].total;

        // Add pagination
        query += ' ORDER BY p.created_at DESC LIMIT ? OFFSET ?';
        params.push(parseInt(limit), offset);

        const [products] = await db.query(query, params);

        res.json({
            success: true,
            data: products,
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
 * Get single product by ID
 * GET /api/products/:id
 */
exports.getProductById = async (req, res, next) => {
    try {
        const { id } = req.params;

        const [products] = await db.query(
            'SELECT * FROM products WHERE id = ?',
            [id]
        );

        if (products.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Product not found'
            });
        }

        // Get all images
        const [images] = await db.query(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC',
            [id]
        );

        const product = {
            ...products[0],
            images
        };

        res.json({
            success: true,
            data: product
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Create new product (Admin only)
 * POST /api/products
 */
exports.createProduct = async (req, res, next) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                message: errors.array()[0].msg
            });
        }

        const { title, slug, description, price, quantity, tags = '' } = req.body;
        const status = parseInt(quantity) > 0 ? 'available' : 'sold_out';

        const [result] = await db.query(
            'INSERT INTO products (title, slug, description, price, quantity, status, tags) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [title, slug, description, parseFloat(price), parseInt(quantity), status, tags]
        );

        res.status(201).json({
            success: true,
            message: 'Product created successfully',
            data: {
                id: result.insertId
            }
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Update product (Admin only)
 * PUT /api/products/:id
 */
exports.updateProduct = async (req, res, next) => {
    try {
        const { id } = req.params;
        const { title, slug, description, price, quantity, status, tags = '' } = req.body;

        // Check if product exists
        const [existing] = await db.query('SELECT id FROM products WHERE id = ?', [id]);
        if (existing.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Product not found'
            });
        }

        await db.query(
            `UPDATE products 
       SET title = ?, slug = ?, description = ?, price = ?, quantity = ?, status = ?, tags = ?, updated_at = CURRENT_TIMESTAMP 
       WHERE id = ?`,
            [title, slug, description, parseFloat(price), parseInt(quantity), status, tags, id]
        );

        res.json({
            success: true,
            message: 'Product updated successfully'
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Delete product (Admin only)
 * DELETE /api/products/:id
 */
exports.deleteProduct = async (req, res, next) => {
    try {
        const { id } = req.params;

        // Get product images to delete files
        const [images] = await db.query(
            'SELECT filename FROM product_images WHERE product_id = ?',
            [id]
        );

        // Delete product (images will cascade)
        const [result] = await db.query('DELETE FROM products WHERE id = ?', [id]);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'Product not found'
            });
        }

        // Delete image files
        for (const image of images) {
            try {
                await fs.unlink(path.join(process.env.UPLOAD_PATH || './uploads', image.filename));
            } catch (err) {
                console.error('Error deleting image file:', err);
            }
        }

        res.json({
            success: true,
            message: 'Product deleted successfully'
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Upload product images (Admin only)
 * POST /api/products/:id/images
 */
exports.uploadImages = async (req, res, next) => {
    try {
        const { id } = req.params;
        const files = req.files;

        if (!files || files.length === 0) {
            return res.status(400).json({
                success: false,
                message: 'No files uploaded'
            });
        }

        // Check if product exists
        const [products] = await db.query('SELECT id FROM products WHERE id = ?', [id]);
        if (products.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Product not found'
            });
        }

        // Check if product has any images
        const [existingImages] = await db.query(
            'SELECT COUNT(*) as count FROM product_images WHERE product_id = ?',
            [id]
        );
        const isPrimary = existingImages[0].count === 0 ? 1 : 0;

        // Insert image records
        const imageIds = [];
        for (let i = 0; i < files.length; i++) {
            const [result] = await db.query(
                'INSERT INTO product_images (product_id, filename, is_primary) VALUES (?, ?, ?)',
                [id, files[i].filename, i === 0 ? isPrimary : 0]
            );
            imageIds.push(result.insertId);
        }

        res.status(201).json({
            success: true,
            message: 'Images uploaded successfully',
            data: {
                imageIds
            }
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Delete product image (Admin only)
 * DELETE /api/products/:id/images/:imageId
 */
exports.deleteImage = async (req, res, next) => {
    try {
        const { id, imageId } = req.params;

        // Get image info
        const [images] = await db.query(
            'SELECT * FROM product_images WHERE id = ? AND product_id = ?',
            [imageId, id]
        );

        if (images.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Image not found'
            });
        }

        const image = images[0];

        // Delete from database
        await db.query('DELETE FROM product_images WHERE id = ?', [imageId]);

        // Delete file
        try {
            await fs.unlink(path.join(process.env.UPLOAD_PATH || './uploads', image.filename));
        } catch (err) {
            console.error('Error deleting image file:', err);
        }

        res.json({
            success: true,
            message: 'Image deleted successfully'
        });
    } catch (error) {
        next(error);
    }
};
