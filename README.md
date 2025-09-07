# CodematicInci Plugin

A comprehensive Shopware 6 plugin for managing cosmetic INCI (International Nomenclature of Cosmetic Ingredients) with AI-powered content generation using OpenAI's ChatGPT.

## Features

### 📋 INCI Ingredient Management
- Complete ingredient database with detailed information
- Support for both English and Polish names
- Alternative names, CAS numbers, and technical data
- Safety ratings and origin classification (Natural/Synthetic)
- Main functions categorization

### 🤖 AI-Powered Content Generation
- Automatic description generation using OpenAI ChatGPT
- Safety information with scientific backing
- Rating system (Good/Average/Bad) with explanations
- Technical field population (CAS numbers, alternative names, etc.)
- Configurable prompts through admin panel

### 🌐 Frontend Display
- **Listing Page** (`/inci`) - Alphabetical ingredient list with table of contents
- **Detail Pages** (`/inci/{slug}`) - Complete ingredient information
- Responsive design with clean, professional layout
- SEO-optimized with proper meta tags and structured data
- Multi-language support (English, Polish, German)

### 🛠️ CLI Management
Complete command-line interface for ingredient management:
- `codematic:inci:add "Name"` - Add new ingredient
- `codematic:inci:generate "Name"` - Generate AI content
- `codematic:inci:show "Name"` - Display all ingredient details
- `codematic:inci:remove "Name"` - Remove ingredient
- `codematic:inci:clear [--force]` - Remove all ingredients

## Installation

1. **Download** the plugin to `custom/plugins/CodematicInci/`

2. **Install** the plugin:
```bash
bin/console plugin:refresh
bin/console plugin:install --activate CodematicInci
```

3. **Run migrations**:
```bash
bin/console database:migrate --all CodematicInci
```

4. **Configure OpenAI** in `.env.local`:
```env
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-4.1
```

5. **Clear cache**:
```bash
bin/console cache:clear
```

## Configuration

### Admin Panel Settings
Configure all settings in **Settings > Extensions > CodematicInci**:

#### Meta Information
- Meta title template with placeholders `{name}`, `{polishName}`
- Default meta description for ingredients without descriptions
- Listing page meta tags

#### Page Content
- Listing page title and description
- Empty list messages
- Content customization

#### AI Prompts (Fully Configurable)
- **Description Prompt** - Generate ingredient descriptions in Polish with HTML formatting
- **Safety Information Prompt** - Generate safety information based on scientific facts
- **Main Functions Prompt** - Categorize ingredient functions
- **Rating Prompt** - Assign safety ratings (1-3 scale)
- **General Fields Prompt** - Generate technical data (CAS, alternative names, etc.)

All prompts support placeholders like `{INGREDIENT_NAME}`, `{RATING}` and can be customized per sales channel.

## Usage Examples

### Adding and Generating Content
```bash
# Add a new ingredient
bin/console codematic:inci:add "Pentylene Glycol"

# Generate all AI content for the ingredient
bin/console codematic:inci:generate "Pentylene Glycol"

# View complete ingredient details
bin/console codematic:inci:show "Pentylene Glycol"
```

### Viewing in Frontend
- **All ingredients**: `https://yoursite.com/inci`
- **Specific ingredient**: `https://yoursite.com/inci/pentylene-glycol`

## Database Schema

The plugin creates a `codematic_inci` table with the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `name` | VARCHAR | Ingredient name (required) |
| `slug` | VARCHAR | URL-friendly slug (required) |
| `polish_name` | VARCHAR | Polish translation |
| `alternative_names` | TEXT | Comma-separated alternative names |
| `cas_number` | VARCHAR | Chemical Abstracts Service number |
| `description` | TEXT | HTML-formatted description |
| `main_functions` | TEXT | Comma-separated functions |
| `safety_information` | TEXT | HTML-formatted safety info |
| `rating` | INT | Safety rating (1=Good, 2=Average, 3=Bad) |
| `resources` | TEXT | Comma-separated URLs |
| `natural` | BOOLEAN | Natural vs synthetic origin |
| `active` | BOOLEAN | Visibility flag |
| `created_at` | DATETIME | Creation timestamp |
| `updated_at` | DATETIME | Last modification |

## Frontend Features

### Listing Page
- **Alphabetical navigation** - Quick jump to any letter
- **Simple list layout** - Clean, scannable format
- **Ingredient preview** - Name, Polish name, main functions, safety rating
- **Responsive design** - Works on all devices

### Detail Page
- **Complete information display** with proper HTML formatting
- **Safety rating** prominently displayed with color coding
- **Information table** - Name, Polish name, CAS number, origin, functions
- **Additional resources** as clickable links
- **SEO optimization** - Proper meta tags, Open Graph, JSON-LD structured data

### CSS Classes for Customization
- `.inci` - General INCI container
- `.inci-listing` - Listing page specific
- `.inci-detail` - Detail page specific
- `.rating-indicator` - Safety rating badges
- `.rating-indicator-small` - Smaller rating badges for listings

## Technical Details

### Architecture
- **Shopware 6 Plugin Structure** - Follows Shopware best practices
- **Entity System** - Uses Shopware's Data Abstraction Layer (DAL)
- **Service Pattern** - OpenAI integration through dedicated service
- **Command Pattern** - CLI commands for management
- **Page Loader Pattern** - Proper meta information handling

### OpenAI Integration
- **Configurable models** - Support for GPT-4.1, o1, o3, etc.
- **Error handling** - Retry logic with exponential backoff
- **Parameter optimization** - Different parameters for different models
- **Logging** - Comprehensive error and debug logging

### Multi-language Support
- **Snippet system** - All UI text translatable
- **Currently supported**: English, Polish, German
- **Easy to extend** for additional languages

## Development

### File Structure
```
src/
├── Command/                 # CLI commands
├── Core/Content/Inci/      # Entity definitions
├── Migration/              # Database migrations
├── Resources/
│   ├── config/            # Services and configuration
│   ├── snippet/           # Translations
│   └── views/             # Twig templates
├── Service/               # Business logic services
└── Storefront/            # Frontend controllers and page loaders
```

### Extending the Plugin
1. **Add new fields** - Extend `InciDefinition` and create migration
2. **Customize prompts** - Modify prompts in admin configuration
3. **Add languages** - Create new snippet files
4. **Styling** - Use `.inci`, `.inci-detail`, `.inci-listing` CSS classes

## Requirements

- **Shopware 6.4+**
- **PHP 8.1+**
- **OpenAI API access**
- **MySQL/MariaDB**

## Support

For issues, feature requests, or contributions, please refer to the project documentation or contact the development team.

## License

This plugin is proprietary software developed for specific use cases. Please refer to the license terms provided with your copy.