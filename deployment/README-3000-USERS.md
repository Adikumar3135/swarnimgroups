# Swarnim Groups production hardening and 3000-user plan

This package improves the application layer, but 3000 concurrent users cannot be guaranteed by PHP code alone. Use a production stack with HTTPS, PHP-FPM, a shared MySQL server and at least two application nodes behind a load balancer.

## Recommended topology

Internet -> CDN/WAF -> Nginx load balancer -> 2+ PHP-FPM application nodes -> MySQL primary (+ replica for read-heavy reporting) -> Redis (optional shared sessions/cache)

### Application nodes
- PHP 8.2+ with OPcache enabled
- PHP-FPM with a tested worker limit; do not blindly set pm.max_children to 3000
- Keep uploads on shared object storage or a shared volume if multiple nodes serve them
- Keep `.env` outside version control
- Use HTTPS so Secure cookies and geolocation work
- Use a real domain instead of localhost

### Database
- MySQL 8/MariaDB 10.6+
- InnoDB
- Keep `innodb_buffer_pool_size` around 60-70% of dedicated DB RAM as a starting point, then benchmark
- Add read replicas only after measuring read pressure
- Back up daily and test restores
- Run `mysql-performance.sql` after reviewing your current indexes

### Load balancing
`nginx-load-balancer.conf` contains a starting example. Use health checks and least-connections. The app itself should remain stateless except for the PHP session. For multiple app nodes, use Redis-backed sessions or sticky sessions; Redis-backed sessions are preferable.

### Security checklist
- HTTPS only
- Strong DB password and a DB account limited to this database
- `display_errors=Off` in production
- `log_errors=On`
- `expose_php=Off`
- `.env` and SQL files blocked from HTTP
- WAF/rate limiting at the edge
- Rotate admin credentials
- Keep PHP and OS packages patched
- Never trust browser prices, stock or coupon totals; checkout recalculates them server-side
- Product uploads are MIME-checked and randomised
- All state-changing APIs use prepared statements and CSRF tokens

### PWA / notifications
The install prompt is browser-controlled. The app includes a web manifest and service worker. Employee assignment notifications use the browser Notification API and an in-page notification center. The operating system/browser decides the default notification sound; websites cannot force a user's system notification sound.

For true notifications when the employee has closed the browser, add Web Push with VAPID/FCM or another push provider. Polling cannot wake a closed browser.

### Benchmark before launch
Test progressively: 50 -> 100 -> 250 -> 500 -> 1000 -> 2000 -> 3000 concurrent sessions. Measure p95/p99 latency, PHP-FPM saturation, DB CPU/IO, slow queries, error rate and memory. Do not treat 3000 as achieved until the production-sized environment passes those tests.
