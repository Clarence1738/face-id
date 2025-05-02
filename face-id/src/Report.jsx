import React, { useEffect, useState } from 'react';
import './Report.css';

export default function Report() {
  const [checkins, setCheckins] = useState([]);

  useEffect(() => {
    fetch("http://localhost/backend/report.php")
      .then(res => res.json())
      .then(data => setCheckins(data))
      .catch(err => {
        console.error("Error fetching report:", err);
        setCheckins([]);
      });
  }, []);

  return (
    <div className="report-container">
      <h2>Check-In Report</h2>
      {checkins.length === 0 ? (
        <p>No check-ins yet.</p>
      ) : (
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Phone</th>
              <th>Check-in Time</th>
            </tr>
          </thead>
          <tbody>
            {checkins.map((item, index) => (
              <tr key={index}>
                <td>{item.name}</td>
                <td>{item.phone}</td>
                <td>{new Date(item.checkin_time).toLocaleString()}</td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
