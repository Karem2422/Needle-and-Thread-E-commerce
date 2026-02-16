const jwt = require('jsonwebtoken');

/**
 * Middleware to verify JWT token
 */
const verifyToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

    if (!token) {
        return res.status(401).json({
            success: false,
            message: 'Access denied. No token provided.'
        });
    }

    try {
        const decoded = jwt.verify(token, process.env.JWT_SECRET);
        req.user = decoded; // Add user info to request
        next();
    } catch (error) {
        return res.status(403).json({
            success: false,
            message: 'Invalid or expired token.'
        });
    }
};

/**
 * Middleware to check if user is admin
 */
const requireAdmin = (req, res, next) => {
    if (!req.user) {
        return res.status(401).json({
            success: false,
            message: 'Authentication required.'
        });
    }

    if (!req.user.isAdmin) {
        return res.status(403).json({
            success: false,
            message: 'Admin access required.'
        });
    }

    next();
};

/**
 * Optional auth - adds user to request if token exists, but doesn't require it
 */
const optionalAuth = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (token) {
        try {
            const decoded = jwt.verify(token, process.env.JWT_SECRET);
            req.user = decoded;
        } catch (error) {
            // Token invalid, but we don't fail - just continue without user
            req.user = null;
        }
    }

    next();
};

module.exports = {
    verifyToken,
    requireAdmin,
    optionalAuth
};
