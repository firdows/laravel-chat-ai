# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 + Vue 3 + Inertia.js + TypeScript application that implements an AI chat interface using the Prism PHP SDK. The application features real-time chat with AI models (OpenAI GPT-4.1-nano), tools/function calling, and persistent chat history.

## Development Commands

### Frontend (Node.js/Vite)
- `npm run dev` - Start Vite development server
- `npm run build` - Build for production  
- `npm run build:ssr` - Build with SSR support
- `npm run lint` - Run ESLint with auto-fix
- `npm run format` - Format code with Prettier
- `npm run format:check` - Check code formatting

### Backend (PHP/Laravel)
- `composer dev` - Start full development environment (server + queue + vite)
- `composer dev:ssr` - Start with SSR support (server + queue + logs + ssr)  
- `composer test` - Run test suite
- `php artisan serve` - Start Laravel development server
- `php artisan queue:listen` - Start queue worker
- `php artisan test` - Run PHPUnit/Pest tests
- `php artisan migrate` - Run database migrations

### Testing
- Uses Pest PHP for testing framework
- Tests located in `tests/Feature/` and `tests/Unit/`
- Run with `composer test` or `php artisan test`

### Code Quality
- Frontend: ESLint + Prettier with TypeScript support
- Backend: Laravel Pint for PHP code styling
- TypeScript checking with `vue-tsc`

## Architecture

### Backend Structure
- **Controllers**: Primary application logic in `app/Http/Controllers/`
  - `ChatController` - AI chat functionality with Prism SDK integration
  - `DashboardController` - Main dashboard
  - `TodoController` - Todo management
  - `TestController` - Development/testing endpoints
- **Models**: `User`, `Todo`, `History` for chat persistence
- **Database**: SQLite for development, supports migration to other databases
- **AI Integration**: Uses Prism PHP SDK for OpenAI integration with tools/function calling

### Frontend Structure  
- **Framework**: Vue 3 + Composition API + TypeScript
- **Routing**: Inertia.js for SPA-like experience
- **UI**: Reka UI component library + Tailwind CSS v4
- **Build**: Vite with Laravel integration
- **Entry Points**: 
  - `resources/js/app.ts` - Main application
  - `resources/js/ssr.ts` - Server-side rendering

### Key Features
- **AI Chat**: Real-time streaming chat with OpenAI models
- **Tools/Function Calling**: Weather, user search, and API integration tools
- **Chat History**: Persistent conversation history with 2-hour context window
- **Authentication**: Laravel Breeze with Inertia.js
- **Queue System**: For background job processing

### Environment Configuration
- Copy `.env.example` to `.env` for initial setup
- Requires OpenAI API key configuration
- Uses SQLite database by default (file: `database/database.sqlite`)

### Routes Structure
- `/` - Welcome page
- `/dashboard` - Main dashboard (authenticated)
- `/chat` - AI chat interface (authenticated)  
- `/todos` - Todo management (authenticated)
- `/test` - Development/testing endpoints
- `/about` - About page
- Settings routes in `routes/settings.php`
- Auth routes in `routes/auth.php`

### Database Schema
- `users` - User authentication
- `histories` - Chat message persistence with JSON parts field
- `todos` - Todo list functionality
- Standard Laravel cache and job tables

## Development Notes

- Frontend uses Vue 3 Composition API with `<script setup>` syntax
- TypeScript is configured throughout the frontend stack
- Inertia.js provides seamless backend-frontend integration
- Prism SDK handles AI model interactions with proper error handling and streaming
- Chat history uses Carbon date filtering for context management
- All authenticated routes require email verification