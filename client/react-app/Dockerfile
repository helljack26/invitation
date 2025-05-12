# Dockerfile.dev
FROM node:18-alpine

WORKDIR /app

# install deps
COPY package*.json ./
RUN npm install --legacy-peer-deps

# copy source
COPY . .

# expose Next.js dev port
EXPOSE 3000

# run dev server (with fast-refresh)
CMD ["npm", "run", "dev"]


# # 1) install deps & build
# FROM node:18-alpine AS builder
# WORKDIR /app
# COPY package*.json ./
# RUN npm install --legacy-peer-deps
# COPY . .
# RUN npm run build

# # 2) production runtime
# FROM node:18-alpine AS runner
# WORKDIR /app
# ENV NODE_ENV=production
# COPY --from=builder /app/package*.json ./
# RUN npm install --omit=dev --legacy-peer-deps
# COPY --from=builder /app/.next ./.next
# COPY --from=builder /app/public ./public
# COPY --from=builder /app/next.config.js ./next.config.js
# EXPOSE 3000
# CMD ["npx","next","start"]
