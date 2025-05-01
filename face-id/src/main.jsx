import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import Register from './Registration.jsx'

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <Register />
  </StrictMode>,
)
