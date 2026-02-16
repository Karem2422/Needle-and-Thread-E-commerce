# Needle & Thread Backend - Quick Setup Guide

## Prerequisites

- Node.js 18+ installed
- MySQL 8.0+ installed and running
- XAMPP (for MySQL) or standalone MySQL

## Quick Start (5 minutes)

### Step 1: Install Dependencies

```bash
cd c:\xampp\htdocs\needle-and-thread\backend
npm install
```

This will install all required packages:
- express, mysql2, bcryptjs, jsonwebtoken, dotenv, cors, multer, express-validator, helmet, morgan

### Step 2: Set Up Database

**Option A: Using phpMyAdmin (Easiest)**
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "Import" tab
3. Choose file: `migrations/001_create_tables.sql`
4. Click "Go"

**Option B: Using MySQL Command Line**
```bash
mysql -u root -p < migrations/001_create_tables.sql
```

This creates:
- Database: `needle_thread`
- 10 tables with sample data
- Admin user (email: admin@needlethread.com)

### Step 3: Set Admin Password

The admin user needs a proper password hash. Run this in Node.js:

```bash
node -e "const bcrypt = require('bcryptjs'); bcrypt.hash('admin123', 10).then(hash => console.log(hash));"
```

Copy the output hash, then run this SQL in phpMyAdmin:

```sql
UPDATE needle_thread.users 
SET password_hash = 'PASTE_HASH_HERE' 
WHERE email = 'admin@needlethread.com';
```

### Step 4: Start Server

```bash
npm run dev
```

You should see:
```
🚀 ============================================
🚀 Needle & Thread API Server
🚀 Environment: development
🚀 Server running on port 3000
🚀 API URL: http://localhost:3000
🚀 ============================================
```

### Step 5: Test API

Open browser or use cURL:

**Health Check:**
```
http://localhost:3000/health
```

**Get Products:**
```
http://localhost:3000/api/products
```

**Login:**
```bash
curl -X POST http://localhost:3000/api/auth/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@needlethread.com\",\"password\":\"admin123\"}"
```

## Configuration

The `.env` file is already configured for local development:

```env
NODE_ENV=development
PORT=3000
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=needle_thread
JWT_SECRET=needle-thread-jwt-secret-key-2024-change-in-production
JWT_EXPIRES_IN=7d
```

**For production**, change:
- `JWT_SECRET` to a random string
- `DB_PASSWORD` to your MySQL password
- `CORS_ORIGIN` to your Flutter app domain

## API Endpoints Summary

### Public Endpoints (No Auth Required)
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login
- `GET /api/products` - List products
- `GET /api/products/:id` - Get product details

### User Endpoints (Auth Required)
- `GET /api/auth/me` - Get profile
- `POST /api/orders` - Create order
- `GET /api/orders` - Get my orders
- `POST /api/products/:id/comments` - Add review

### Admin Endpoints (Admin Auth Required)
- `GET /api/admin/stats` - Dashboard stats
- `POST /api/products` - Create product
- `PUT /api/products/:id` - Update product
- `DELETE /api/products/:id` - Delete product
- `POST /api/products/:id/images` - Upload images
- `PUT /api/orders/:id/status` - Update order status
- `GET /api/admin/users` - List users
- `GET /api/admin/inventory-logs` - View inventory logs

## Testing with Postman

1. **Login as Admin:**
   - POST `http://localhost:3000/api/auth/login`
   - Body: `{"email":"admin@needlethread.com","password":"admin123"}`
   - Copy the `token` from response

2. **Use Token:**
   - Add header: `Authorization: Bearer YOUR_TOKEN`
   - Now you can access protected endpoints

3. **Create Product:**
   - POST `http://localhost:3000/api/products`
   - Headers: `Authorization: Bearer YOUR_TOKEN`
   - Body: 
   ```json
   {
     "title": "New Bag",
     "slug": "new-bag",
     "description": "Beautiful handmade bag",
     "price": 199.99,
     "quantity": 10,
     "tags": "bag,handmade"
   }
   ```

## Troubleshooting

### "Database connection failed"
- Make sure MySQL is running (start XAMPP)
- Check database credentials in `.env`
- Verify database `needle_thread` exists

### "Port 3000 already in use"
- Change `PORT=3001` in `.env`
- Or kill process: `taskkill /F /IM node.exe` (Windows)

### "Cannot find module"
- Run `npm install` again
- Delete `node_modules` and `package-lock.json`, then `npm install`

### "Invalid token"
- Token might be expired (default: 7 days)
- Login again to get new token

## Next Steps

1. ✅ Backend is ready!
2. 📱 Now build the Flutter mobile app
3. 🔗 Connect Flutter app to this backend API
4. 🚀 Deploy both to production

## File Structure

```
backend/
├── config/
│   └── database.js          ✅ MySQL connection
├── controllers/
│   ├── authController.js    ✅ Login/Register
│   ├── productsController.js ✅ Product CRUD
│   ├── ordersController.js  ✅ Order management
│   ├── commentsController.js ✅ Reviews
│   └── adminController.js   ✅ Admin features
├── middleware/
│   ├── auth.js              ✅ JWT verification
│   └── errorHandler.js      ✅ Error handling
├── routes/
│   ├── auth.js              ✅ Auth routes
│   ├── products.js          ✅ Product routes
│   ├── orders.js            ✅ Order routes
│   ├── comments.js          ✅ Comment routes
│   └── admin.js             ✅ Admin routes
├── migrations/
│   └── 001_create_tables.sql ✅ Database schema
├── uploads/                 ✅ Product images
├── .env                     ✅ Configuration
├── package.json             ✅ Dependencies
├── server.js                ✅ Main server
└── README.md                ✅ Documentation
```

## Support

For detailed API documentation, see `README.md`.

Happy coding! 🚀
