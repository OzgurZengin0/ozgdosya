const express = require('express');
const fs = require('fs');
const path = require('path');
const multer = require('multer');
const { exec } = require('child_process');

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// GitHub Repo Ayarları
const REPO_PATH = path.join(__dirname, 'my-repo'); // Local repo dizini
const USERS_FILE = path.join(REPO_PATH, 'users.json');

// Multer Ayarları (Dosya Yükleme)
const storage = multer.diskStorage({
    destination: (req, file, cb) => cb(null, path.join(REPO_PATH, 'uploads')),
    filename: (req, file, cb) => cb(null, `${Date.now()}-${file.originalname}`)
});
const upload = multer({ storage });

// GitHub'a Değişiklikleri Push Et
const pushToGitHub = async (message) => {
    exec(`cd ${REPO_PATH} && git add . && git commit -m "${message}" && git push`, (err, stdout, stderr) => {
        if (err) {
            console.error(`Git push error: ${err.message}`);
        }
        console.log(stdout || stderr);
    });
};

// Kullanıcıları Yükle
const loadUsers = () => {
    if (!fs.existsSync(USERS_FILE)) {
        fs.writeFileSync(USERS_FILE, JSON.stringify([]));
    }
    const data = fs.readFileSync(USERS_FILE);
    return JSON.parse(data);
};

// Kullanıcıları Kaydet
const saveUsers = (users) => {
    fs.writeFileSync(USERS_FILE, JSON.stringify(users, null, 2));
};

// Kayıt Ol
app.post('/register', (req, res) => {
    const { username, password } = req.body;
    const users = loadUsers();

    if (users.find((user) => user.username === username)) {
        return res.status(400).json({ message: 'Username already exists' });
    }

    users.push({ username, password });
    saveUsers(users);

    pushToGitHub('New user registered');
    res.status(201).json({ message: 'User registered successfully' });
});

// Giriş Yap
app.post('/login', (req, res) => {
    const { username, password } = req.body;
    const users = loadUsers();

    const user = users.find((u) => u.username === username && u.password === password);
    if (!user) {
        return res.status(401).json({ message: 'Invalid username or password' });
    }

    res.status(200).json({ message: 'Login successful' });
});

// Dosya Yükleme
app.post('/upload', upload.single('file'), (req, res) => {
    pushToGitHub('File uploaded');
    res.status(201).json({ message: 'File uploaded successfully', filename: req.file.filename });
});

// Kullanıcı Dosyalarını Listele
app.get('/files/:username', (req, res) => {
    const userDir = path.join(REPO_PATH, 'uploads', req.params.username);
    if (!fs.existsSync(userDir)) {
        return res.status(404).json({ message: 'No files found for this user' });
    }

    const files = fs.readdirSync(userDir).map((file) => ({
        name: file,
        url: `/uploads/${req.params.username}/${file}`
    }));
    res.status(200).json(files);
});

// Sunucuyu Başlat
const PORT = 5000;
app.listen(PORT, () => console.log(`Server running on port ${PORT}`));
