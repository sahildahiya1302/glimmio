const express = require('express');
const path = require('path');
const helmet = require('helmet');
const session = require('express-session');

const app = express();
app.use(helmet());
app.use(session({
  secret: process.env.SESSION_SECRET || 'change_me',
  resave: false,
  saveUninitialized: false,
  cookie: { httpOnly: true, sameSite: 'lax' }
}));

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));
app.use(express.static(path.join(__dirname, 'public')));

app.get('/', (req, res) => {
  res.redirect('/dashboard');
});

app.get('/dashboard', (req, res) => {
  res.render('dashboard', { role: req.session.role || '' });
});

const port = process.env.PORT || 3000;
app.listen(port, () => {
  console.log(`IMX server running on port ${port}`);
});
