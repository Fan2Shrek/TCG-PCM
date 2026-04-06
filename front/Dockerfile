# ============================================
# Stage 1: Dependencies Installation Stage
# ============================================

# IMPORTANT: Node.js Version Maintenance
# This Dockerfile uses Node.js 24.13.0-slim, which was the latest LTS version at the time of writing.
# To ensure security and compatibility, regularly update the NODE_VERSION ARG to the latest LTS version.
ARG NODE_VERSION=24.13.0-slim

FROM node:${NODE_VERSION} AS dependencies

# Set working directory
WORKDIR /app

# Copy package-related files first to leverage Docker's caching mechanism
COPY package.json yarn.lock* package-lock.json* pnpm-lock.yaml* .npmrc* ./

# Install project dependencies with frozen lockfile for reproducible builds
RUN npm ci --prefer-offline --no-audit --progress=false

# ============================================
# Stage 2: Build Next.js application in standalone mode
# ============================================

FROM node:${NODE_VERSION} AS builder

# Set working directory
WORKDIR /app

# Copy project dependencies from dependencies stage
COPY --from=dependencies /app/node_modules ./node_modules

# Copy application source code
COPY . .

ENV NODE_ENV=production

# Next.js collects completely anonymous telemetry data about general usage.
# Learn more here: https://nextjs.org/telemetry
# Uncomment the following line in case you want to disable telemetry during the build.
# ENV NEXT_TELEMETRY_DISABLED=1

# Build Next.js application
RUN npm run build

# ============================================
# stage 3: run next.js application
# ============================================

from node:${NODE_VERSION} as runner

# set working directory
workdir /app

# set production environment variables
env node_env=production
env port=3000
env hostname="0.0.0.0"

# next.js collects completely anonymous telemetry data about general usage.
# learn more here: https://nextjs.org/telemetry
# uncomment the following line in case you want to disable telemetry during the run time.
# env next_telemetry_disabled=1

# copy production assets
copy --from=builder --chown=node:node /app/public ./public

# set the correct permission for prerender cache
run mkdir .next
run chown node:node .next

# automatically leverage output traces to reduce image size
# https://nextjs.org/docs/advanced-features/output-file-tracing
copy --from=builder --chown=node:node /app/.next/standalone ./
copy --from=builder --chown=node:node /app/.next/static ./.next/static

# if you want to persist the fetch cache generated during the build so that
# cached responses are available immediately on startup, uncomment this line:
# copy --from=builder --chown=node:node /app/.next/cache ./.next/cache

# switch to non-root user for security best practices
user node

# expose port 3000 to allow http traffic
expose 3000

# start next.js standalone server
cmd ["node", "server.js"]

from node:${node_version} as dev

RUN npm install

CMD ["npm", "run", "dev", "--", "-H", "0.0.0.0"]
