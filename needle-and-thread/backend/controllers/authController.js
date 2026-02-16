const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const { validationResult } = require('express-validator');
const db = require('../config/database');

/**
 * Register new user
 * POST /api/auth/register
 */
exports.register = async (req, res, next) => {
    try {
        // Validate request
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                message: errors.array()[0].msg
            });
        }

        const { name, email, password } = req.body;

        // Check if user already exists
        const [existing] = await db.query(
            'SELECT id FROM users WHERE email = ?',
            [email]
        );

        if (existing.length > 0) {
            return res.status(400).json({
                success: false,
                message: 'Email already registered'
            });
        }

        // Hash password
        const salt = await bcrypt.genSalt(10);
        const passwordHash = await bcrypt.hash(password, salt);

        // Create user
        const [result] = await db.query(
            'INSERT INTO users (name, email, password_hash, is_admin) VALUES (?, ?, ?, ?)',
            [name, email, passwordHash, 0]
        );

        res.status(201).json({
            success: true,
            message: 'Registration successful',
            data: {
                userId: result.insertId
            }
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Login user
 * POST /api/auth/login
 */
exports.login = async (req, res, next) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                message: errors.array()[0].msg
            });
        }

        const { email, password } = req.body;

        // Find user
        const [users] = await db.query(
            'SELECT * FROM users WHERE LOWER(email) = LOWER(?)',
            [email]
        );

        if (users.length === 0) {
            return res.status(401).json({
                success: false,
                message: 'Invalid email or password'
            });
        }

        const user = users[0];

        // Verify password
        const isValidPassword = await bcrypt.compare(password, user.password_hash);
        if (!isValidPassword) {
            return res.status(401).json({
                success: false,
                message: 'Invalid email or password'
            });
        }

        // Generate JWT token
        const token = jwt.sign(
            {
                userId: user.id,
                email: user.email,
                isAdmin: Boolean(user.is_admin)
            },
            process.env.JWT_SECRET,
            { expiresIn: process.env.JWT_EXPIRES_IN || '7d' }
        );

        res.json({
            success: true,
            message: 'Login successful',
            data: {
                token,
                user: {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    isAdmin: Boolean(user.is_admin)
                }
            }
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Get current user profile
 * GET /api/auth/me
 */
exports.getProfile = async (req, res, next) => {
    try {
        const [users] = await db.query(
            'SELECT id, name, email, is_admin, created_at FROM users WHERE id = ?',
            [req.user.userId]
        );

        if (users.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        const user = users[0];

        res.json({
            success: true,
            data: {
                id: user.id,
                name: user.name,
                email: user.email,
                isAdmin: Boolean(user.is_admin),
                createdAt: user.created_at
            }
        });
    } catch (error) {
        next(error);
    }
};

/**
 * Request password reset
 * POST /api/auth/forgot-password
 */
exports.forgotPassword = async (req, res, next) => {
    try {
        const { email } = req.body;

        // Check if user exists
        const [users] = await db.query(
            'SELECT id FROM users WHERE email = ?',
            [email]
        );

        // Always return success (security: don't reveal if email exists)
        res.json({
            success: true,
            message: 'If the email exists, a password reset link has been sent'
        });

        if (users.length > 0) {
            // TODO: Generate reset token and send email
            // For now, this is a placeholder
            console.log('Password reset requested for:', email);
        }
    } catch (error) {
        next(error);
    }
};

/**
 * Reset password
 * POST /api/auth/reset-password
 */
exports.resetPassword = async (req, res, next) => {
    try {
        const { token, newPassword } = req.body;

        // TODO: Verify reset token and update password
        // This is a placeholder implementation

        res.json({
            success: true,
            message: 'Password reset successful'
        });
    } catch (error) {
        next(error);
    }
};
