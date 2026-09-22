#!/bin/bash

echo "🚀 Setting up Studio Photography App..."
echo ""

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
  echo " Installing dependencies..."
  npm install
  echo ""
fi

# Generate Prisma client
echo "🔧 Generating Prisma client..."
npx prisma generate
echo ""

# Push database schema
echo "🗄️  Setting up database..."
npx prisma db push
echo ""

# Seed database
echo "🌱 Seeding database with sample data..."
npm run db:seed
echo ""

echo "✅ Setup complete!"
echo ""
echo "To start the development server:"
echo "  npm run dev"
echo ""
echo "Then open http://localhost:3000"
echo ""
echo "Admin dashboard: http://localhost:3000/admin"
