# Car Sales Plugin

## Overview

This is a WordPress plugin for managing car sales with license plate lookup functionality, user dashboards, financing calculators, and admin management tools. The plugin integrates with external services for vehicle data retrieval and provides both public-facing features and administrative controls.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **WordPress Plugin Structure**: Built as a WordPress plugin with separate admin and public-facing components
- **CSS Framework**: Custom CSS with modern design patterns including CSS Grid, Flexbox, and gradient backgrounds
- **JavaScript Framework**: jQuery-based with AJAX functionality for dynamic interactions
- **Responsive Design**: Mobile-first approach with grid layouts that adapt to different screen sizes

### Backend Architecture
- **WordPress Plugin Framework**: Utilizes WordPress hooks, actions, and filters
- **AJAX Handlers**: Server-side endpoints for handling asynchronous requests
- **Nonce Security**: WordPress nonce implementation for CSRF protection
- **Database Integration**: Likely uses WordPress custom post types and meta fields for car listings

## Key Components

### Public-Facing Features
1. **License Plate Search**: 
   - Danish license plate validation (format: AB12345)
   - AJAX-powered lookup functionality
   - Integration with Synsbasen API for comprehensive vehicle data
   - Real-time search results display with detailed vehicle information

2. **Car Search Interface**:
   - Modern search container with card-based design
   - Inspired by caro.dk design patterns
   - Advanced filtering capabilities

3. **User Dashboard**:
   - User account management
   - Personal car listings management
   - Inquiry tracking

4. **Financing Calculator**:
   - Interactive loan calculation tools
   - Real-time financial projections

### Administrative Features
1. **Car Approval System**:
   - Bulk and individual car approval workflows
   - Status management for listings
   - Admin notification system

2. **Analytics Dashboard**:
   - Statistics visualization with gradient card designs
   - Performance metrics tracking
   - Grid-based layout for statistics display

3. **Inquiry Management**:
   - Customer inquiry tracking
   - Response management tools

## Data Flow

1. **License Plate Lookup Flow**:
   - User enters Danish license plate → Frontend validation → AJAX request → WordPress backend → Synsbasen API call → Data processing → Response to frontend → Display comprehensive vehicle details

2. **Car Approval Flow**:
   - Admin selects cars for approval → AJAX request with nonce → Backend processing → Database update → Success/error response → UI update

3. **Bilinfo Sync**:
   - Automated or manual synchronization with external vehicle database
   - Data enrichment for car listings

## External Dependencies

### Core Dependencies
- **WordPress**: Core CMS framework with custom post types and meta fields
- **jQuery**: Frontend JavaScript framework for DOM manipulation and AJAX
- **Synsbasen API**: Primary Danish vehicle data service for comprehensive license plate lookups and inspection history
- **Bilinfo API**: External vehicle marketplace for car approval and synchronization

### Design Dependencies
- **CSS Grid & Flexbox**: Modern layout systems for responsive design
- **Font Stack**: System fonts (-apple-system, BlinkMacSystemFont, Segoe UI, Roboto)

## Deployment Strategy

### WordPress Plugin Deployment
- **Plugin Structure**: Standard WordPress plugin architecture with admin and public directories
- **Asset Management**: Separate CSS and JavaScript files for admin and public interfaces
- **Security**: WordPress nonce implementation for secure AJAX requests
- **Activation Hooks**: Likely includes database table creation and default settings setup

### File Organization
- `/admin/`: Administrative interface files (CSS, JS)
- `/public/`: Public-facing interface files (CSS, JS)
- Core plugin files in root directory (not shown but implied)

### Performance Considerations
- **AJAX Optimization**: Asynchronous loading for better user experience
- **CSS Optimization**: Modular stylesheets for different components
- **Caching Strategy**: Likely implements WordPress caching hooks for car data

## Recent Changes: Latest modifications with dates

### July 28, 2025 - Synsbasen API Integration
- **Updated API Integration**: Replaced Danish Motor Registry with Synsbasen API as primary vehicle data source
- **Enhanced Data Structure**: Modified to support Synsbasen's comprehensive vehicle data format including:
  - Detailed emission information (CO2, Euro norm, energy class)
  - Comprehensive inspection history and validity dates
  - Technical specifications (engine size, power in HP/kW, weight)
  - Registration status and first registration dates
  - VIN numbers and detailed vehicle characteristics
- **Class Restructure**: Renamed `Danish_Motor_Registry` class to `Synsbasen_Integration`
- **API Endpoint Updates**: Modified to use Synsbasen's search endpoint with proper query structure
- **Demo Enhancement**: Updated frontend demo to display comprehensive vehicle information from Synsbasen format
- **Documentation Updates**: Updated project documentation to reflect Synsbasen as primary data source