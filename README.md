# Face-ID Recognition System

[![CI](https://github.com/Clarence1738/face-id/actions/workflows/ci.yml/badge.svg)](https://github.com/Clarence1738/face-id/actions/workflows/ci.yml)

A modern face recognition system built with face-api.js and TensorFlow.js, featuring a React frontend and PHP backend for real-time facial recognition and attendance tracking.

## 🎯 Project Overview

This project implements a face recognition system that can:
- Register new faces with user information
- Recognize faces in real-time using webcam
- Track attendance and generate reports
- Provide a modern web-based interface

## ✨ Key Features & Results

- **Real-time Recognition**: Fast face detection and recognition using TensorFlow.js
- **User Management**: Easy registration and management of user profiles
- **Attendance Tracking**: Automated check-in/check-out system
- **Modern UI**: Clean, responsive React interface built with Vite
- **Cross-platform**: Runs in any modern web browser

## 📸 Demo

![Demo](demo/demo.gif)

*Demo GIF showing face registration and recognition workflow*

## 🏗️ Architecture

### Frontend (React + Vite)
- **face-api.js**: Face detection, recognition, and landmark detection
- **TensorFlow.js**: Machine learning inference in the browser
- **React Router**: Navigation and routing
- **Vite**: Fast development and optimized builds

### Backend (PHP)
- **recognize.php**: Face recognition endpoint
- **register.php**: User registration endpoint
- **report.php**: Generate attendance reports
- **checkin.php**: Handle check-in/check-out events

### Data Flow
1. User captures face via webcam
2. Frontend extracts facial descriptors using face-api.js
3. Descriptors sent to PHP backend for storage/comparison
4. Backend returns match results or stores new registration
5. Frontend displays results and updates UI

## 🚀 Quick Start

### Prerequisites
- Modern web browser (Chrome, Firefox, Edge recommended)
- PHP 7.4+ (for backend)
- Node.js 14+ (for frontend development)
- Webcam access

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/Clarence1738/face-id.git
   cd face-id
   ```

2. **Install frontend dependencies**
   ```bash
   cd face-id
   npm install
   ```

3. **Set up backend**
   ```bash
   # Ensure PHP is installed and configured
   # Backend files are in the 'backend' directory
   ```

4. **Run the application**
   ```bash
   # Start frontend development server
   cd face-id
   npm run dev
   
   # In a separate terminal, serve the backend
   # (Example using PHP built-in server)
   cd backend
   php -S localhost:8000
   ```

5. **Access the application**
   - Frontend: http://localhost:5173
   - Backend API: http://localhost:8000

### Testing

```bash
# Install test dependencies
pip install -r requirements.txt

# Run tests
pytest tests/
```

## 💡 How I Contributed

This project demonstrates several key software engineering skills:

- **Full-stack Development**: Integrated frontend (React/JavaScript) with backend (PHP)
- **Machine Learning Integration**: Implemented browser-based ML using TensorFlow.js and face-api.js
- **Real-time Processing**: Handled webcam streams and real-time face detection
- **API Design**: Created RESTful endpoints for face recognition operations
- **Modern Tooling**: Used Vite for fast development, npm for package management
- **User Experience**: Designed intuitive registration and recognition workflows

## 📋 Project Structure

```
face-id/
├── face-id/               # Frontend React application
│   ├── src/              # React components and logic
│   ├── public/           # Static assets
│   └── package.json      # Frontend dependencies
├── backend/              # PHP backend
│   ├── recognize.php     # Face recognition endpoint
│   ├── register.php      # Registration endpoint
│   ├── report.php        # Reporting endpoint
│   └── checkin.php       # Check-in/check-out logic
├── tests/                # Test suite
├── demo/                 # Demo materials and screenshots
├── .github/workflows/    # CI/CD configuration
└── README.md            # This file
```

## 🧪 Development

### Running Tests
```bash
pytest tests/ -v
```

### Linting
```bash
cd face-id
npm run lint
```

### Building for Production
```bash
cd face-id
npm run build
```

## 📝 Future Enhancements

- [ ] Add authentication and authorization
- [ ] Implement database storage instead of file-based
- [ ] Add more comprehensive test coverage
- [ ] Deploy to cloud platform (AWS, Azure, or Heroku)
- [ ] Add Docker containerization
- [ ] Implement face mask detection
- [ ] Add multi-language support

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📧 Contact

**Clarence**
- GitHub: [@Clarence1738](https://github.com/Clarence1738)
- Project Link: [https://github.com/Clarence1738/face-id](https://github.com/Clarence1738/face-id)

---

*This project showcases practical application of machine learning, web development, and software engineering best practices. Feel free to explore, fork, and contribute!*
