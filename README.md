# ip-ban-system-php
Simple IP ban system in PHP. Uses [GeoJS](https://www.geojs.io).
# Set up
Create a database with the following structure:

```
CREATE TABLE `ip_bans` (
  `id` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `ban_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ban_until` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason` varchar(256) NOT NULL DEFAULT '[no reason given]'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
```

# Todo
Simple dashboard to insert rows.
