const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const path = require('path');
const fs = require('fs');
require('dotenv').config();

const { errorHandler, notFound } = require('./middleware/errorHandler');

// Import routes
const authRoutes = require('./routes/auth');
const productsRoutes = require('./routes/products');
const ordersRoutes = require('./routes/orders');
const commentsRoutes = require('./routes/comments');
const adminRoutes = require('./routes/admin');

// Initialize express app
const app = express();

// Create uploads directory if it doesn't exist
const uploadsDir = process.env.UPLOAD_PATH || './uploads';
if (!fs.existsSync(uploadsDir)) {
    fs.mkdirSync(uploadsDir, { recursive: true });
    console.log(`✅ Created uploads directory: ${uploadsDir}`);
}

// Middleware
app.use(helmet()); // Security headers
app.use(cors({
    origin: process.env.CORS_ORIGIN || '*',
    credentials: true
}));
app.use(express.json()); // Parse JSON bodies
app.use(express.urlencoded({ extended: true })); // Parse URL-encoded bodies
app.use(morgan(process.env.NODE_ENV === 'development' ? 'dev' : 'combined')); // Logging

// Serve uploaded files statically
app.use('/uploads', express.static(uploadsDir));

// API Routes
app.use('/api/auth', authRoutes);
app.use('/api/products', productsRoutes);
app.use('/api/orders', ordersRoutes);
app.use('/api', commentsRoutes); // Comments routes include /products/:id/comments
app.use('/api/admin', adminRoutes);

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        success: true,
        message: 'Server is running',
        timestamp: new Date().toISOString()
    });
});

// Root endpoint
app.get('/', (req, res) => {
    res.json({
        success: true,
        message: 'Needle & Thread API',
        version: '1.0.0',
        endpoints: {
            auth: '/api/auth',
            products: '/api/products',
            orders: '/api/orders',
            comments: '/api/products/:productId/comments',
            admin: '/api/admin'
        }
    });
});

// 404 handler
app.use(notFound);

// Error handler (must be last)
app.use(errorHandler);

// Start server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log('');
    console.log('🚀 ============================================');
    console.log(`🚀 Needle & Thread API Server`);
    console.log(`🚀 Environment: ${process.env.NODE_ENV || 'development'}`);
    console.log(`🚀 Server running on port ${PORT}`);
    console.log(`🚀 API URL: http://localhost:${PORT}`);
    console.log('🚀 ============================================');
    console.log('');
});

module.exports = app;
