# Use a specific Node.js version
FROM node:18-alpine

# Set the working directory inside the container
WORKDIR /app

# Copy package files first (for caching layers)
COPY package.json package-lock.json ./

# Install dependencies
RUN npm install --legacy-peer-deps

# Copy the app source code
COPY . .

# Build the Next.js app
RUN npm run build

# Expose the port Next.js runs on
EXPOSE 3000

# Start Next.js server
CMD ["npm", "run", "dev"]
