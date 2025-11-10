const nodemailer = require('nodemailer');
const fs = require('fs');
const path = require('path');

// Parse form data from env (JSON string)
const formData = JSON.parse(process.env.FORM_DATA || '{}');
const {
    name, email, phone, sponsorship_type, amount, dedication, 'preferred-shiur': preferredShiur, 'parsha-name': parshaName
} = formData;

// Basic validation
if (!name || !email || !phone || !sponsorship_type || !amount) {
    console.error('Missing required fields');
    process.exit(1);
}

// SMTP Transporter (Gmail)
const transporter = nodemailer.createTransporter({
    host: 'smtp.gmail.com',
    port: 587,
    secure: false,
    auth: {
        user: process.env.GMAIL_USER,
        pass: process.env.GMAIL_PASS
    }
});

// Email 1: Thank-You to User
const thankYouTemplate = fs.readFileSync(path.join(__dirname, 'thank_you_template.html'), 'utf8');
let thankYouBody = thankYouTemplate
    .replace('{{name}}', name)
    .replace('{{amount}}', `$${amount}`)
    .replace('{{sponsorship_type}}', sponsorship_type.charAt(0).toUpperCase() + sponsorship_type.slice(1));

await transporter.sendMail({
    from: `"Rabbi Kraz's Shiurim" <${process.env.GMAIL_USER}>`,
    to: `${name} <${email}>`,
    subject: 'Thank You for Sponsoring a Shiur!',
    html: thankYouBody
});
console.log('Thank-you email sent');

// Email 2: Admin Notification
const adminTemplate = fs.readFileSync(path.join(__dirname, 'admin_email_template.html'), 'utf8');
let adminBody = adminTemplate
    .replace('{{name}}', name)
    .replace('{{email}}', email)
    .replace('{{phone}}', phone)
    .replace('{{sponsorship_type}}', sponsorship_type.charAt(0).toUpperCase() + sponsorship_type.slice(1))
    .replace('{{amount}}', `$${amount}`)
    .replace('{{dedication}}', dedication || 'None')
    .replace('{{preferred_shiur}}', preferredShiur || 'Any Upcoming Shiur')
    .replace('{{parsha_name}}', parshaName || 'N/A');

await transporter.sendMail({
    from: `"Rabbi Kraz's Shiurim" <${process.env.GMAIL_USER}>`,
    to: process.env.ADMIN_EMAIL || 'rabbikraz1@gmail.com',
    subject: 'New Sponsorship Form Submission',
    html: adminBody
});
console.log('Admin email sent');

transporter.close();
