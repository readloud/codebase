// src/components/CategoryCard.jsx
import React from 'react';
import { Link } from 'react-router-dom';

export default function CategoryCard({ category }) {
  return (
    <Link to={`/products?category=${category.id}`} className="group">
      <div className="bg-white dark:bg-gray-800 rounded-lg p-4 text-center hover:shadow-md transition-shadow">
        {category.icon && (
          <img src={category.icon} alt={category.name} className="h-12 w-12 mx-auto mb-2" />
        )}
        <h3 className="text-sm font-medium text-gray-900 dark:text-white">{category.name}</h3>
      </div>
    </Link>
  );
}