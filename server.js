const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const multer = require('multer');
const mongoose = require('mongoose');
const path = require('path');
const cors = require('cors');

const app = express();
app.use(express.json());
app.use(cors());
app.use(express.static(path.join(__dirname, '../frontend')));

// MongoDB bağlantısı
mongoose.connect('mongodb://localhost/user_files', { useNewUrlParser: true, useUnifiedTopology: true })
  .then(() => console.log('Connected to MongoDB'))
  .catch(err => console.error('Failed to connect to MongoDB', err));

// Kullanıcı modelini oluştur
const userSchema = new mongoose.Schema({
  username: String,
  password: String,
  files: [String] // Dosya isimleri
});

const User = mongoose.model('User', userSchema);

// Multer ayarları
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'uploads/');
  },
  filename: (req, file, cb) => {
    cb(null, Date.now() + '-' + file.originalname);
  }
});
const upload = multer({ storage: storage });

// Kayıt Olma API'si
app.post('/api/register', async (req, res) => {
  const { username, password } = req.body;
  const hashedPassword = await bcrypt.hash(password, 10);
  const user = new User({ username, password: hashedPassword });
  await user.save();
  res.status(201).send('User registered');
});

// Giriş Yapma API'si
app.post('/api/login', async (req, res) => {
  const { username, password } = req.body;
  const user = await User.findOne({ username });
  if (!user) return res.status(400).send('User not found');
  
  const validPassword = await bcrypt.compare(password, user.password);
  if (!validPassword) return res.status(400).send('Invalid password');
  
  const token = jwt.sign({ userId: user._id }, 'your_jwt_secret');
  res.json({ token });
});

// Dosya Yükleme API'si
app.post('/api/upload', upload.single('file'), async (req, res) => {
  const token = req.headers['authorization'];
  if (!token) return res.status(401).send('Unauthorized');
  
  const decoded = jwt.verify(token, 'your_jwt_secret');
  const user = await User.findById(decoded.userId);
  user.files.push(req.file.filename);
  await user.save();
  
  res.send('File uploaded');
});

// Dosya İndirme API'si
app.get('/api/download/:filename', async (req, res) => {
  const { filename } = req.params;
  const token = req.headers['authorization'];
  if (!token) return res.status(401).send('Unauthorized');
  
  const decoded = jwt.verify(token, 'your_jwt_secret');
  const user = await User.findById(decoded.userId);
  if (!user.files.includes(filename)) return res.status(403).send('File not found');
  
  res.download(`uploads/${filename}`);
});

// Server başlatma
app.listen(5000, () => console.log('Backend server is running on port 5000'));const express = require('express');
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const multer = require('multer');
const mongoose = require('mongoose');
const path = require('path');
const cors = require('cors');

const app = express();
app.use(express.json());
app.use(cors());
app.use(express.static(path.join(__dirname, '../frontend')));

// MongoDB bağlantısı
mongoose.connect('mongodb://localhost/user_files', { useNewUrlParser: true, useUnifiedTopology: true })
  .then(() => console.log('Connected to MongoDB'))
  .catch(err => console.error('Failed to connect to MongoDB', err));

// Kullanıcı modelini oluştur
const userSchema = new mongoose.Schema({
  username: String,
  password: String,
  files: [String] // Dosya isimleri
});

const User = mongoose.model('User', userSchema);

// Multer ayarları
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'uploads/');
  },
  filename: (req, file, cb) => {
    cb(null, Date.now() + '-' + file.originalname);
  }
});
const upload = multer({ storage: storage });

// Kayıt Olma API'si
app.post('/api/register', async (req, res) => {
  const { username, password } = req.body;
  const hashedPassword = await bcrypt.hash(password, 10);
  const user = new User({ username, password: hashedPassword });
  await user.save();
  res.status(201).send('User registered');
});

// Giriş Yapma API'si
app.post('/api/login', async (req, res) => {
  const { username, password } = req.body;
  const user = await User.findOne({ username });
  if (!user) return res.status(400).send('User not found');
  
  const validPassword = await bcrypt.compare(password, user.password);
  if (!validPassword) return res.status(400).send('Invalid password');
  
  const token = jwt.sign({ userId: user._id }, 'your_jwt_secret');
  res.json({ token });
});

// Dosya Yükleme API'si
app.post('/api/upload', upload.single('file'), async (req, res) => {
  const token = req.headers['authorization'];
  if (!token) return res.status(401).send('Unauthorized');
  
  const decoded = jwt.verify(token, 'your_jwt_secret');
  const user = await User.findById(decoded.userId);
  user.files.push(req.file.filename);
  await user.save();
  
  res.send('File uploaded');
});

// Dosya İndirme API'si
app.get('/api/download/:filename', async (req, res) => {
  const { filename } = req.params;
  const token = req.headers['authorization'];
  if (!token) return res.status(401).send('Unauthorized');
  
  const decoded = jwt.verify(token, 'your_jwt_secret');
  const user = await User.findById(decoded.userId);
  if (!user.files.includes(filename)) return res.status(403).send('File not found');
  
  res.download(`uploads/${filename}`);
});

// Server başlatma
app.listen(5000, () => console.log('Backend server is running on port 5000'));
