INSERT INTO customers 
    (customer_name, contact_name, address, city, 
        postcode, country) 
    VALUES (':customer_name', ':contact_name',
        ':address', ':city', 
        ':postcode', ':country' );