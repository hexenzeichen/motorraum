select p1.date, sum(p1.quantity * pl1.price) as total from price_log pl1 right join (select p.product_id, p.date, max(pl.date) maxdate from products p left join price_log pl on (p.product_id = pl.product_id and (p.date = pl.date or pl.date < p.date)) group by p.product_id, p.date) d on (pl1.product_id = d.product_id and (pl1.date = d.maxdate)) left join products p1 on (p1.product_id = d.product_id and p1.date = d.date) group by p1.date having p1.date between '2020-01-01' and '2020-01-10';

