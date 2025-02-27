# Use a lightweight Node.js image
FROM node:18-alpine

# Set working directory
WORKDIR /app

# Copy package.json first to leverage Docker layer caching
COPY package.json package-lock.json ./

# Install dependencies
RUN npm install --legacy-peer-deps

# Copy all project files
COPY . .

# Ensure all dependencies are properly installed
RUN npm rebuild && npm install @babel/runtime

# Expose Next.js port
EXPOSE 3000

# Start the Next.js app
CMD ["npm", "run", "dev"]
