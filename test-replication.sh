#!/bin/bash

# 在主節點創建測試表和數據
docker exec mysql_master mysql -uroot -proot wordpress -e "
CREATE TABLE IF NOT EXISTS test_replication (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO test_replication (name) VALUES ('Test 1');
"

echo "主節點數據:"
docker exec mysql_master mysql -uroot -proot wordpress -e "SELECT * FROM test_replication;"

sleep 2

echo "從節點1數據:"
docker exec mysql_slave1 mysql -uroot -proot wordpress -e "SELECT * FROM test_replication;"

echo "從節點2數據:"
docker exec mysql_slave2 mysql -uroot -proot wordpress -e "SELECT * FROM test_replication;" 