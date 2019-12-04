# News Fetcher

This application aims to crawl websites of various news websites with the goal of fetching new posts. 

## The end goal

After collecting news from various providers, all post items will be aggregated, resulting in a single parent post with contents composed out of the multiple variations.

After analyzing news, in order to create a link between existing news, tags and important information will be extracted from source posts, such as:
- important dates
- proper names
- references to events

## Testing
First run all migrations (so that test db is created):
- `bin/console doctrine:migrations:migrate`

Then load fixtures:
- `bin/console doctrine:fixtures:load`

Finally, run tests using the Symfony bridge:
- `bin/phpunit`



