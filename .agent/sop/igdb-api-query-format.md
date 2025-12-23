# SOP: IGDB API Query Format

## Problem
IGDB API requests returning empty results despite valid authentication.

## Root Cause
The Apicalypse query language has strict formatting requirements that were not being followed:

1. **Multi-line queries can fail** - The API prefers single-line, semicolon-delimited queries
2. **Spaces after commas** - Fields should not have spaces after commas in the field list
3. **Query normalization** - Newlines and extra whitespace can break queries

## Solution

### 1. Query Format (Correct)
```
fields name,slug,cover.image_id;where cover != null;sort total_rating desc;limit 50;
```

### 2. Query Format (Incorrect)
```
fields name, slug, cover.image_id;
where cover != null;
sort total_rating desc;
limit 50;
```

### 3. Implementation Changes

**Added query normalization in `IgdbService::normalizeQuery()`:**
```php
protected function normalizeQuery(string $query): string
{
    // Replace newlines with spaces
    $query = preg_replace('/\s*\n\s*/', ' ', $query);

    // Replace multiple spaces with single space
    $query = preg_replace('/\s+/', ' ', $query);

    // Trim whitespace
    $query = trim($query);

    return $query;
}
```

**Changed query construction from HEREDOC to string concatenation:**
```php
// Before (problematic)
$body = <<<QUERY
fields name, slug;
limit 50;
QUERY;

// After (working)
$body = "fields name,slug;";
$body .= "limit 50;";
```

### 4. Debugging Tips

1. **Test connection first:**
   ```bash
   php artisan igdb:test
   ```

2. **Enable debug logging:**
   Check `storage/logs/laravel.log` for the exact query being sent.

3. **Simplest working query:**
   ```
   fields name;limit 5;
   ```

### 5. IGDB API Rules

- Each clause must end with a semicolon `;`
- No spaces after commas in field lists
- `search` cannot be combined with `sort`
- Maximum `limit` is 500
- Rate limit: 4 requests per second
- Use `&` for AND, `|` for OR in where clauses
- Null check syntax: `field != null`

## Files Modified
- `app/Services/IgdbService.php` - Added query normalization, fixed query format
- `app/Console/Commands/TestIgdbConnection.php` - New test command

## Verification
Run `php artisan igdb:test` to verify the fix works correctly.

## Date
2024-12-23

## Related
- [IGDB API Documentation](https://api-docs.igdb.com/)
- [Apicalypse Query Language](https://github.com/igdb/node-apicalypse)
