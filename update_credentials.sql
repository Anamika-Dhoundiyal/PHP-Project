-- Update Admin Password to 123
UPDATE admin SET password = MD5('123') WHERE username = 'Admin';

-- Update or Insert Customer Account for Anamika with password 123
UPDATE customers SET password = MD5('123') WHERE username = 'Anamika';

-- If the customer doesn't exist, insert it
INSERT IGNORE INTO customers (name, email, password, username) 
VALUES ('Anamika', 'anamika@example.com', MD5('123'), 'Anamika');

-- Verify the updates
SELECT username, password FROM admin WHERE username = 'Admin';
SELECT username, password FROM customers WHERE username = 'Anamika';
