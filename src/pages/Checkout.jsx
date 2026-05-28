// src/pages/Checkout.jsx
import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import axios from 'axios';
import toast from 'react-hot-toast';

export default function Checkout() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [cart, setCart] = useState([]);
  const [loading, setLoading] = useState(false);
  const [paymentMethod, setPaymentMethod] = useState('balance');
  const [checkoutItem, setCheckoutItem] = useState(null);

  useEffect(() => {
    // Check if single item checkout
    const item = localStorage.getItem('checkoutItem');
    if (item) {
      setCheckoutItem(JSON.parse(item));
      localStorage.removeItem('checkoutItem');
    } else {
      // Load cart
      const savedCart = JSON.parse(localStorage.getItem('cart') || '[]');
      setCart(savedCart);
    }
  }, []);

  const getTotal = () => {
    if (checkoutItem) {
      return checkoutItem.price * checkoutItem.quantity;
    }
    return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  };

  const handleCheckout = async () => {
    if (!user) {
      toast.error('Please login first');
      navigate('/login');
      return;
    }

    setLoading(true);

    try {
      if (checkoutItem) {
        // Single item checkout
        const response = await axios.post('/api/checkout', {
          product_id: checkoutItem.productId,
          customer_phone: checkoutItem.customerPhone,
          customer_email: checkoutItem.customerEmail,
          payment_method: paymentMethod
        });

        if (response.data.payment_url) {
          window.location.href = response.data.payment_url;
        } else {
          toast.success('Order placed successfully!');
          navigate('/orders');
        }
      } else {
        // Multiple items checkout
        for (const item of cart) {
          await axios.post('/api/checkout', {
            product_id: item.id,
            customer_phone: user.phone,
            customer_email: user.email,
            payment_method: paymentMethod
          });
        }
        localStorage.removeItem('cart');
        toast.success('Orders placed successfully!');
        navigate('/orders');
      }
    } catch (error) {
      toast.error(error.response?.data?.message || 'Checkout failed');
    } finally {
      setLoading(false);
    }
  };

  if (!user) {
    return (
      <div className="text-center py-12">
        <p className="text-gray-600 dark:text-gray-400 mb-4">Please login to checkout</p>
        <button
          onClick={() => navigate('/login')}
          className="bg-blue-600 text-white px-6 py-2 rounded-lg"
        >
          Login
        </button>
      </div>
    );
  }

  return (
    <div className="max-w-4xl mx-auto">
      <h1 className="text-3xl font-bold text-gray-900 dark:text-white mb-6">Checkout</h1>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
        {/* Order Summary */}
        <div className="bg-white dark:bg-gray-800 rounded-lg p-6">
          <h2 className="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Order Summary</h2>
          
          {checkoutItem ? (
            <div className="flex justify-between py-2 border-b dark:border-gray-700">
              <div>
                <p className="font-medium text-gray-900 dark:text-white">{checkoutItem.name}</p>
                <p className="text-sm text-gray-500">Quantity: {checkoutItem.quantity}</p>
              </div>
              <p className="font-medium text-gray-900 dark:text-white">
                Rp {(checkoutItem.price * checkoutItem.quantity).toLocaleString('id-ID')}
              </p>
            </div>
          ) : (
            cart.map((item, index) => (
              <div key={index} className="flex justify-between py-2 border-b dark:border-gray-700">
                <div>
                  <p className="font-medium text-gray-900 dark:text-white">{item.name}</p>
                  <p className="text-sm text-gray-500">Quantity: {item.quantity}</p>
                </div>
                <p className="font-medium text-gray-900 dark:text-white">
                  Rp {(item.price * item.quantity).toLocaleString('id-ID')}
                </p>
              </div>
            ))
          )}

          <div className="flex justify-between pt-4 mt-4 border-t dark:border-gray-700">
            <p className="text-lg font-bold text-gray-900 dark:text-white">Total</p>
            <p className="text-lg font-bold text-blue-600">
              Rp {getTotal().toLocaleString('id-ID')}
            </p>
          </div>
        </div>

        {/* Payment Method */}
        <div className="bg-white dark:bg-gray-800 rounded-lg p-6">
          <h2 className="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Payment Method</h2>
          
          <div className="space-y-3">
            <label className="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
              <input
                type="radio"
                name="payment"
                value="balance"
                checked={paymentMethod === 'balance'}
                onChange={(e) => setPaymentMethod(e.target.value)}
                className="mr-3"
              />
              <div>
                <p className="font-medium text-gray-900 dark:text-white">Wallet Balance</p>
                <p className="text-sm text-gray-500">Current balance: Rp {user?.balance?.toLocaleString('id-ID')}</p>
              </div>
            </label>

            <label className="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
              <input
                type="radio"
                name="payment"
                value="midtrans"
                checked={paymentMethod === 'midtrans'}
                onChange={(e) => setPaymentMethod(e.target.value)}
                className="mr-3"
              />
              <div>
                <p className="font-medium text-gray-900 dark:text-white">Credit Card / Bank Transfer</p>
                <p className="text-sm text-gray-500">Pay with Midtrans</p>
              </div>
            </label>
          </div>

          {paymentMethod === 'balance' && getTotal() > (user?.balance || 0) && (
            <p className="text-red-500 text-sm mt-3">Insufficient balance. Please top up or choose another payment method.</p>
          )}

          <button
            onClick={handleCheckout}
            disabled={loading || (paymentMethod === 'balance' && getTotal() > (user?.balance || 0))}
            className="w-full mt-6 bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 transition"
          >
            {loading ? 'Processing...' : 'Place Order'}
          </button>
        </div>
      </div>
    </div>
  );
}