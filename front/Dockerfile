# =========================
# BASE
# =========================
FROM node:20-alpine AS base
WORKDIR /app

FROM base AS deps
COPY package.json package-lock.json ./
RUN npm ci

# =========================
# DEV
# =========================
FROM deps AS dev
COPY . .
CMD ["npm", "run", "dev", "--", "-H", "0.0.0.0"]

FROM deps AS builder

COPY . .

ARG API_URL
ENV NEXT_PUBLIC_API_URL=${API_URL}

ARG MERCURE_URL
ENV NEXT_PUBLIC_MERCURE_URL=${MERCURE_URL}

RUN npm run build

# =========================
# PROD
# =========================
FROM node:20-alpine AS prod
WORKDIR /app

ENV NODE_ENV=production

RUN addgroup -S nodejs && adduser -S nextjs -G nodejs

COPY --from=builder /app/public ./public
COPY --from=builder /app/.next/standalone ./
COPY --from=builder /app/.next/static ./.next/static

USER nextjs

EXPOSE 3000

# Start app
CMD ["node", "server.js"]
