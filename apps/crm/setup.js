const mysql = require('mysql2');
const bcrypt = require('bcryptjs');
const fs = require('fs');
const path = require('path');
require('dotenv').config();

// Create connection
const connection = mysql.createConnection({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASS || '',
});

async function setupDatabase() {
    try {
        console.log('Connecting to MySQL...');
        await connection.promise().connect();
        
        // Create database if not exists
        console.log('Creating database...');
        await connection.promise().execute(`CREATE DATABASE IF NOT EXISTS ${process.env.DB_NAME || 'crm_system'}`);
        await connection.promise().execute(`USE ${process.env.DB_NAME || 'crm_system'}`);
        
        // Read and execute schema
        console.log('Creating tables...');
        const schemaPath = path.join(__dirname, 'database', 'schema.sql');
        const schema = fs.readFileSync(schemaPath, 'utf8');
        
        // Split and execute SQL statements
        const statements = schema.split(';').filter(stmt => stmt.trim());
        for (const statement of statements) {
            if (statement.trim() && !statement.trim().startsWith('--')) {
                try {
                    await connection.promise().execute(statement);
                } catch (err) {
                    // Ignore errors for existing tables
                    if (!err.message.includes('already exists')) {
                        console.warn('Warning:', err.message);
                    }
                }
            }
        }
        
        // Check if admin user exists
        const [rows] = await connection.promise().execute('SELECT * FROM users WHERE username = ?', ['admin']);
        
        if (rows.length === 0) {
            // Create default admin user
            console.log('Creating default admin user...');
            const hashedPassword = await bcrypt.hash('admin123', 10);
            await connection.promise().execute(
                'INSERT INTO users (username, email, password, role, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)',
                ['admin', 'admin@company.com', hashedPassword, 'admin', 'System', 'Administrator']
            );
            console.log('Default admin user created:');
            console.log('Username: admin');
            console.log('Password: admin123');
        } else {
            console.log('Admin user already exists');
        }
        
        console.log('Database setup completed successfully!');
        
    } catch (error) {
        console.error('Error setting up database:', error);
    } finally {
        connection.end();
    }
}

setupDatabase();
