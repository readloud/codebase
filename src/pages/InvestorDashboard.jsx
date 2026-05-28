// src/pages/InvestorDashboard.jsx
import React, { useState, useEffect } from 'react';
import { useAuth } from '../contexts/AuthContext';
import axios from 'axios';
import toast from 'react-hot-toast';

export default function InvestorDashboard() {
  const { user } = useAuth();
  const [investments, setInvestments] = useState([]);
  const [profits, setProfits] = useState([]);
  const [totalInvested, setTotalInvested] = useState(0);
  const [totalProfit, setTotalProfit] = useState(0);
  const [loading, setLoading] = useState(true);
  const [investAmount, setInvestAmount] = useState('');

  useEffect(() => {
    fetchInvestorData();
  }, []);

  const fetchInvestorData = async () => {
    try {
      const response = await axios.get('/api/investor/dashboard');
      setInvestments(response.data.investments);
      setProfits(response.data.profits);
      setTotalInvested(response.data.total_invested);
      setTotalProfit(response.data.total_profit);
    } catch (error) {
      console.error('Error fetching investor data:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleInvest = async () => {
    if (!investAmount || investAmount < 100000) {
      toast.error('Minimum investment is Rp 100,000');
      return;
    }

    try {
      const response = await axios.post('/api/investor/invest', {
        amount: parseInt(investAmount)
      });
      
      if (response.data.payment_url) {
        window.location.href = response.data.payment_url;
      } else {
        toast.success('Investment successful!');
        fetchInvestorData();
        setInvestAmount('');
      }
    } catch (error) {
      toast.error(error.response?.data?.message || 'Investment failed');
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div>
      <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-6">Investor Dashboard</h1>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div className="bg-gradient-to-r from-green-500 to-teal-600 rounded-lg p-6 text-white">
          <h3 className="text-lg font-semibold mb-2">Total Invested</h3>
          <p className="text-3xl font-bold">Rp {totalInvested.toLocaleString('id-ID')}</p>
        </div>
        <div className="bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg p-6 text-white">
          <h3 className="text-lg font-semibold mb-2">Total Profit</h3>
          <p className="text-3xl font-bold">Rp {totalProfit.toLocaleString('id-ID')}</p>
        </div>
      </div>

      {/* Invest Form */}
      <div className="bg-white dark:bg-gray-800 rounded-lg p-6 mb-8">
        <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">Make New Investment</h2>
        <div className="flex gap-4">
          <input
            type="number"
            value={investAmount}
            onChange={(e) => setInvestAmount(e.target.value)}
            placeholder="Amount (min Rp 100,000)"
            className="flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          />
          <button
            onClick={handleInvest}
            className="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700"
          >
            Invest Now
          </button>
        </div>
        <p className="text-sm text-gray-500 dark:text-gray-400 mt-2">
          Get 10% monthly profit on your investment
        </p>
      </div>

      {/* Investments List */}
      <div className="bg-white dark:bg-gray-800 rounded-lg p-6 mb-8">
        <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">Your Investments</h2>
        {investments.length === 0 ? (
          <p className="text-gray-500 dark:text-gray-400">No investments yet</p>
        ) : (
          <div className="space-y-4">
            {investments.map((investment) => (
              <div key={investment.id} className="border dark:border-gray-700 rounded-lg p-4">
                <div className="flex justify-between items-center mb-2">
                  <span className="font-semibold text-gray-900 dark:text-white">
                    Rp {investment.amount.toLocaleString('id-ID')}
                  </span>
                  <span className={`px-2 py-1 rounded-full text-xs font-semibold ${
                    investment.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  }`}>
                    {investment.status.toUpperCase()}
                  </span>
                </div>
                <p className="text-sm text-gray-500">
                  Started: {new Date(investment.start_date).toLocaleDateString('id-ID')}
                </p>
                {investment.end_date && (
                  <p className="text-sm text-gray-500">
                    Ends: {new Date(investment.end_date).toLocaleDateString('id-ID')}
                  </p>
                )}
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Profit History */}
      <div className="bg-white dark:bg-gray-800 rounded-lg p-6">
        <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">Profit History</h2>
        {profits.length === 0 ? (
          <p className="text-gray-500 dark:text-gray-400">No profits recorded yet</p>
        ) : (
          <div className="space-y-4">
            {profits.map((profit) => (
              <div key={profit.id} className="flex justify-between items-center border-b dark:border-gray-700 pb-3">
                <div>
                  <p className="font-medium text-gray-900 dark:text-white">
                    Rp {profit.amount.toLocaleString('id-ID')}
                  </p>
                  <p className="text-sm text-gray-500">
                    {new Date(profit.calculated_at).toLocaleDateString('id-ID')}
                  </p>
                </div>
                <span className={`px-2 py-1 rounded-full text-xs font-semibold ${
                  profit.is_paid ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                }`}>
                  {profit.is_paid ? 'Paid' : 'Pending'}
                </span>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}