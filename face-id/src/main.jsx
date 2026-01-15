// main.jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import './styles/index.css';

import Register from './pages/Registration';
import Recognize from './pages/Recognition';
import Report from './pages/Report';

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <Router>
      <Routes>
        <Route path="/" element={<Register />} />
        <Route path="/recognize" element={<Recognize/>} />
        <Route path="/report" element={<Report />} />
      </Routes>
    </Router>
  </React.StrictMode>
);
