UPDATE customers 
    SET customer_name = ':customer_name', 
        contact_name = ':contact_name',
        address = ':address',
        city = ':city',
        postcode = ':postcode',
        country = ':country' 
    WHERE customer_id = :id;