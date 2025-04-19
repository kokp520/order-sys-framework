#!/bin/bash

echo "等待 MySQL 服務啟動..."
sleep 30

# 在主節點創建複製用戶
docker exec mysql_master mysql -uroot -proot -e "
CREATE USER 'repl'@'%' IDENTIFIED WITH mysql_native_password BY 'repl';
GRANT REPLICATION SLAVE ON *.* TO 'repl'@'%';
FLUSH PRIVILEGES;
"

# 獲取主節點狀態
MASTER_STATUS=$(docker exec mysql_master mysql -uroot -proot -e "SHOW MASTER STATUS\G")
CURRENT_LOG=$(echo "$MASTER_STATUS" | grep File | awk '{print $2}')
CURRENT_POS=$(echo "$MASTER_STATUS" | grep Position | awk '{print $2}')

echo "主節點日誌文件: $CURRENT_LOG"
echo "主節點位置: $CURRENT_POS"

# 配置從節點1
docker exec mysql_slave1 mysql -uroot -proot -e "
CHANGE MASTER TO
MASTER_HOST='mysql_master',
MASTER_USER='repl',
MASTER_PASSWORD='repl',
MASTER_LOG_FILE='$CURRENT_LOG',
MASTER_LOG_POS=$CURRENT_POS;
START SLAVE;
"

# 配置從節點2
docker exec mysql_slave2 mysql -uroot -proot -e "
CHANGE MASTER TO
MASTER_HOST='mysql_master',
MASTER_USER='repl',
MASTER_PASSWORD='repl',
MASTER_LOG_FILE='$CURRENT_LOG',
MASTER_LOG_POS=$CURRENT_POS;
START SLAVE;
"

# 檢查從節點狀態
echo "檢查從節點1狀態:"
docker exec mysql_slave1 mysql -uroot -proot -e "SHOW SLAVE STATUS\G"

echo "檢查從節點2狀態:"
docker exec mysql_slave2 mysql -uroot -proot -e "SHOW SLAVE STATUS\G" 