// src/pages/admin/Topup.jsx
import React, { useState } from 'react';
import axios from 'axios';
import toast from 'react-hot-toast';

export default function AdminTopup() {
  const [userId, setUserId] = useState('');
  const [amount, setAmount] = useState('');
  const [description, setDescription] = useState('');
  const [loading, setLoading] = useState(false);
  const [vendorAmount, setVendorAmount] = useState('');
  const [vendorLoading, setVendorLoading] = useState(false);
  const [searchUser, setSearchUser] = useState('');
  const [searchResults, setSearchResults] = useState([]);

  const searchUsers = async () => {
    if (!searchUser) return;
    try {
      const response = await axios.get('/api/admin/users', { params: { search: searchUser } });
      setSearchResults(response.data);
    } catch (error) {
      toast.error('Failed to search users');
    }
  };

  const handleManualTopup = async (e) => {
    e.preventDefault();
    if (!userId || !amount || amount < 1000) {
      toast.error('Please fill all fields correctly');
      return;
    }

    setLoading(true);
    try {
      await axios.post('/api/admin/manual-topup', {
        user_id: userId,
        amount: parseInt(amount),
        description
      });
      toast.success('Topup successful');
      setUserId('');
      setAmount('');
      setDescription('');
      setSearchResults([]);
      setSearchUser('');
    } catch (error) {
      toast.error(error.response?.data?.message || 'Topup failed');
    } finally {
      setLoading(false);
    }
  };

  const handleVendorDeposit = async () => {
    if (!vendorAmount || vendorAmount < 10000) {
      toast.error('Minimum deposit Rp 10,000');
      return;
    }

    setVendorLoading(true);
    try {
      await axios.post('/api/admin/deposit-vendor', {
        vendor: 'digiflazz',
        amount: parseInt(vendorAmount)
      });
      toast.success('Deposit to vendor successful');
      setVendorAmount('');
    } catch (error) {
      toast.error(error.response?.data?.message || 'Deposit failed');
    } finally {
      setVendorLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-bold text-gray-900 dark:text-white">Topup Management</h1>

      {/* Manual Topup to User */}
      <div className="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">Manual Topup to User</h2>
        
        <div className="mb-4">
          <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Search User
          </label>
          <div className="flex gap-2">
            <input
              type="text"
              value={searchUser}
              onChange={(e) => setSearchUser(e.target.value)}
              placeholder="Search by username, email, or phone"
              className="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            <button
              onClick={searchUsers}
              className="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600"
            >
              Search
            </button>
          </div>
          
          {searchResults.length > 0 && (
            <div className="mt-2 border rounded-lg overflow-hidden">
              {searchResults.map((user) => (
                <div
                  key={user.id}
                  onClick={() => {
                    setUserId(user.id);
                    setSearchUser(user.username);
                    setSearchResults([]);
                  }}
                  className="p-3 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer border-b last:border-b-0"
                >
                  <p className="font-medium text-gray-900 dark:text-white">{user.username}</p>
                  <p className="text-sm text-gray-500">{user.email} | {user.phone}</p>
                  <p className="text-sm text-gray-500">Balance: Rp {user.balance?.toLocaleString('id-ID')}</p>
                </div>
              ))}
            </div>
          )}
        </div>

        <form onSubmit={handleManualTopup} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Amount (IDR)
            </label>
            <input
              type="number"
              value={amount}
              onChange={(e) => setAmount(e.target.value)}
              placeholder="Min Rp 1,000"
              className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Description (Optional)
            </label>
            <textarea
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              rows="3"
              className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              placeholder="Reason for topup..."
            />
          </div>

          <button
            type="submit"
            disabled={loading || !userId}
            className="w-full bg-blue-600 text-white py-2 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50"
          >
            {loading ? 'Processing...' : 'Process Topup'}
          </button>
        </form>
      </div>

      {/* Deposit to Vendor */}
      <div className="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">Deposit to Vendor (Digiflazz)</h2>
        
        <div className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Amount (IDR)
            </label>
            <input
              type="number"
              value={vendorAmount}
              onChange={(e) => setVendorAmount(e.target.value)}
              placeholder="Min Rp 10,000"
              className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
          </div>

          <button
            onClick={handleVendorDeposit}
            disabled={vendorLoading}
            className="w-full bg-green-600 text-white py-2 rounded-lg font-semibold hover:bg-green-700 disabled:opacity-50"
          >
            {vendorLoading ? 'Processing...' : 'Deposit to Vendor'}
          </button>
        </div>
      </div>
    </div>
  );
}