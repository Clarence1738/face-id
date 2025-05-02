// main.jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

import Register from './Registration';
import Recognize from './Recognition';
import Report from './Report';

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
