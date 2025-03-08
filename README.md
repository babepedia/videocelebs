# Celebrity Video Scraper

A PHP-based web scraping tool designed to collect and store celebrity video content and related metadata from entertainment websites.

## Overview

This project consists of PHP scripts that systematically scrape websites containing celebrity videos, extracting valuable information such as:
- Video metadata and embed IDs
- Associated celebrity names and profiles
- TV show references
- Image galleries and thumbnails
- Content descriptions and tags

All collected data is stored in a MySQL database for further processing, analysis, or display.

## Features

- Automated scraping of celebrity video posts
- Extraction of embedded video IDs
- Collection of associated images with source URLs
- Metadata parsing (actresses, TV shows, tags)
- Support for pagination to process multiple pages of content
- Proper HTTP request handling with customizable headers

## Requirements

- PHP 7.0+
- MySQL/MariaDB
- PHP extensions:
  - mysqli
  - curl
  - mbstring
- Simple HTML DOM Parser library

## Configuration

Before running the script, you need to:

1. Create a `config.php` file with your database connection details:
```php
<?php
$mysqli = new mysqli('localhost', 'username', 'password', 'database');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
?>
```

2. Ensure the `functions.php` file includes the `func_get_content()` function for HTTP requests

3. Set up the required database tables:
   - `videocelebs_posts`: Stores post information with columns for slug, title, poster, pagedata, and stat

## Usage

The script can be run through a web server with query parameters:

```
# To scrape new posts (specify the number of pages to process)
/path/to/script.php?newposts=10

# To process and extract detailed data from collected posts
/path/to/script.php?pdata=1
```

## Database Structure

The main table structure should include:

```sql
CREATE TABLE `videocelebs_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `title` text,
  `poster` text,
  `pagedata` longtext,
  `stat` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Legal Considerations

This tool is for educational purposes only. Web scraping may be against the terms of service of some websites. Always ensure you:

- Respect website robots.txt files
- Use reasonable request rates to avoid server strain
- Consider copyright and legal restrictions regarding content
- Obtain proper permissions if needed

## License

MIT 2.0

## Contributing

Guidelines for contributing to this project:

1. Fork the repository
2. Create a feature branch
3. Submit a pull request with a clear description of changes

## Disclaimer

The developers of this tool are not responsible for any misuse or violations of terms of service resulting from its use.
