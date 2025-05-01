// main.jsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';

import Register from './Registration';
import Recognize from './Recognition';

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <Router>
      <Routes>
        <Route path="/" element={<Register />} />
        <Route path="/recognize" element={<Recognize/>} />
      </Routes>
    </Router>
  </React.StrictMode>
);
