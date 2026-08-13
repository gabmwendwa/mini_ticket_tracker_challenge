import { useState, useEffect } from 'react';
import './App.css';

// Define the API URL for fetching and managing tickets
const API_URL = 'https://mtt.sungusoft.com/tickets';


function App() {
  const [tickets, setTickets] = useState([]);
  const [loading, setLoading] = useState(true);
  
  // Form state
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [status, setStatus] = useState('o');
  const [priority, setPriority] = useState('l');

  // Fetch tickets on load
  useEffect(() => {
    fetchTickets();
  }, []);

  // Fetch tickets from the API
  const fetchTickets = async () => {
    try {
      const response = await fetch(API_URL);
      const data = await response.json();
      // Adjust based on your API response structure (e.g., data.data or data)
      setTickets(Array.isArray(data) ? data : data.data || []);
    } catch (error) {
      console.error('Error fetching tickets:', error);
    } finally {
      setLoading(false);
    }
  };

  // Helper function to format MySQL timestamp (YYYY-MM-DD HH:MM:SS) to a readable local string
  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    // The 'Z' tells JS to parse as UTC, then it automatically converts to local browser time
    const date = new Date(dateString + 'Z'); 
    return date.toLocaleDateString(undefined, { 
      year: 'numeric', month: 'short', day: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });
  };

  // Handle Create Ticket
  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, description, status, priority })
      });
      const result = await response.json();
      
      if (response.ok) {
        setTitle('');
        setDescription('');
        fetchTickets(); // Refresh list
      } else {
        alert(result.message || 'Failed to create ticket');
      }
    } catch (error) {
      console.error('Error creating ticket:', error);
    }
  };

  // Handle Status and Priority Change
  const handleStatusPriorityChange = async (id, newStatus, newPriority) => {
    try {
      const response = await fetch(`${API_URL}/${id}`, {
        method: 'PATCH', // or POST depending on your PHP router
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: newStatus, priority: newPriority })
      });
      
      if (response.ok) {
        fetchTickets(); // Refresh list
      } else {
        alert('Failed to update status');
      }
    } catch (error) {
      console.error('Error updating status:', error);
    }
  };

  return (
    <div style={{ maxWidth: '900px', margin: '0 auto', padding: '20px', fontFamily: 'Arial' }}>
      <h2>Mini Ticket Tracker</h2>

      {/* Form to create a new ticket */}
      <div style={{ background: '#f9f9f9', padding: '15px', marginBottom: '20px', borderRadius: '5px', border: '1px solid #ddd' }}>
        <h3>Create New Ticket</h3>
        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
          <input 
            type="text" 
            placeholder="Title (Required)" 
            value={title} 
            onChange={(e) => setTitle(e.target.value)} 
            required 
            style={{ padding: '8px' }}
          />
          <textarea 
            placeholder="Description (Optional)" 
            value={description} 
            onChange={(e) => setDescription(e.target.value)} 
            style={{ padding: '8px', height: '60px' }}
          />
          <div style={{ display: 'flex', gap: '20px', alignItems: 'center' }}>
            <label>
              Status: 
              <select value={status} onChange={(e) => setStatus(e.target.value)} style={{ marginLeft: '5px', padding: '5px' }}>
                <option value="o">Open</option>
                <option value="i">In Progress</option>
                <option value="c">Closed</option>
              </select>
            </label>
            <label>
              Priority: 
              <select value={priority} onChange={(e) => setPriority(e.target.value)} style={{ marginLeft: '5px', padding: '5px' }}>
                <option value="l">Low</option>
                <option value="m">Medium</option>
                <option value="h">High</option>
              </select>
            </label>
            <button type="submit" style={{ padding: '8px 20px', background: '#007BFF', color: 'white', border: 'none', cursor: 'pointer', borderRadius: '4px' }}>
              Add Ticket
            </button>
          </div>
        </form>
      </div>

      {/* List of tickets */}
      <h3>Ticket List</h3>
      {loading ? (
        <p>Loading tickets...</p>
      ) : tickets.length === 0 ? (
        <p>No tickets found.</p>
      ) : (
        <table border="1" cellPadding="8" style={{ width: '100%', borderCollapse: 'collapse', borderColor: '#ddd' }}>
          <thead>
            <tr style={{ background: '#f2f2f2' }}>
              <th>ID</th>
              <th>Title</th>
              <th>Created</th> {/* New Column Header */}
              <th>Status</th>
              <th>Priority</th>
            </tr>
          </thead>
          <tbody>
            {tickets.slice().reverse().map((ticket) => (
              <tr key={ticket.id}>
                <td style={{ textAlign: 'center' }}>{ticket.id}</td>
                <td>
                  {ticket.title}
                  {ticket.description && <div style={{ fontSize: '0.85em', color: '#666', marginTop: '2px' }}>{ticket.description}</div>}
                </td>
                {/* New Column Data: Formatted Date */}
                <td style={{ whiteSpace: 'nowrap', textAlign: 'center' }}>
                  {formatDate(ticket.created_at)}
                </td>
                <td style={{ textAlign: 'center' }}>
                  <select 
                    value={ticket.status} 
                    onChange={(e) => handleStatusPriorityChange(ticket.id, e.target.value, ticket.priority)}
                    style={{ padding: '2px' }}
                  >
                    <option value="o">Open</option>
                    <option value="i">In Progress</option>
                    <option value="c">Closed</option>
                  </select>
                </td>
                <td style={{ textAlign: 'center' }}>
                  <select 
                    value={ticket.priority} 
                    onChange={(e) => handleStatusPriorityChange(ticket.id, ticket.status, e.target.value)}
                    style={{ padding: '2px' }}
                  >
                    <option value="l">Low</option>
                    <option value="m">Medium</option>
                    <option value="h">High</option>
                  </select>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}

export default App;