// src/pages/admin/Dashboard.jsx
import React, { useState, useEffect } from 'react';
import axios from 'axios';
import {
  ChartBarIcon,
  ShoppingCartIcon,
  UsersIcon,
  CurrencyDollarIcon
} from '@heroicons/react/24/outline';

export default function AdminDashboard() {
  const [stats, setStats] = useState({
    totalSales: 0,
    totalOrders: 0,
    totalUsers: 0,
    totalProducts: 0
  });
  const [recentOrders, setRecentOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      const [statsRes, ordersRes] = await Promise.all([
        axios.get('/api/admin/reports'),
        axios.get('/api/orders?limit=5')
      ]);
      setStats(statsRes.data);
      setRecentOrders(ordersRes.data.data);
    } catch (error) {
      console.error('Error fetching dashboard data:', error);
    } finally {
      setLoading(false);
    }
  };

  const statsCards = [
    { title: 'Total Sales', value: `Rp ${stats.totalSales.toLocaleString('id-ID')}`, icon: CurrencyDollarIcon, color: 'bg-blue-500' },
    { title: 'Total Orders', value: stats.totalOrders, icon: ShoppingCartIcon, color: 'bg-green-500' },
    { title: 'Total Users', value: stats.totalUsers, icon: UsersIcon, color: 'bg-purple-500' },
    { title: 'Total Products', value: stats.totalProducts, icon: ChartBarIcon, color: 'bg-orange-500' }
  ];

  if (loading) {
    return (
      <div className="flex justify-center items-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div>
      <h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-6">Dashboard</h1>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {statsCards.map((stat, index) => (
          <div key={index} className="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-500 dark:text-gray-400">{stat.title}</p>
                <p className="text-2xl font-bold text-gray-900 dark:text-white">{stat.value}</p>
              </div>
              <div className={`${stat.color} p-3 rounded-lg`}>
                <stat.icon className="h-6 w-6 text-white" />
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Recent Orders */}
      <div className="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
        <h2 className="text-xl font-semibold text-gray-900 dark:text-white mb-4">Recent Orders</h2>
        <div className="overflow-x-auto">
          <table className="min-w-full">
            <thead>
              <tr className="border-b dark:border-gray-700">
                <th className="text-left py-3 px-4 text-gray-600 dark:text-gray-400">Order ID</th>
                <th className="text-left py-3 px-4 text-gray-600 dark:text-gray-400">Customer</th>
                <th className="text-left py-3 px-4 text-gray-600 dark:text-gray-400">Amount</th>
                <th className="text-left py-3 px-4 text-gray-600 dark:text-gray-400">Status</th>
                <th className="text-left py-3 px-4 text-gray-600 dark:text-gray-400">Date</th>
              </tr>
            </thead>
            <tbody>
              {recentOrders.map((order) => (
                <tr key={order.id} className="border-b dark:border-gray-700">
                  <td className="py-3 px-4 text-gray-900 dark:text-white">{order.order_number}</td>
                  <td className="py-3 px-4 text-gray-900 dark:text-white">{order.user?.username}</td>
                  <td className="py-3 px-4 text-gray-900 dark:text-white">Rp {order.amount.toLocaleString('id-ID')}</td>
                  <td className="py-3 px-4">
                    <span className={`px-2 py-1 rounded-full text-xs font-semibold ${
                      order.status === 'success' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                    }`}>
                      {order.status.toUpperCase()}
                    </span>
                  </td>
                  <td className="py-3 px-4 text-gray-500">{new Date(order.created_at).toLocaleDateString()}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
