package main

import (
	"database/sql"
	"fmt"
	"log"
	"math/rand"
	"os"
	"runtime"
	"strconv"
	"strings"
	"sync"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

// 配置參數
type Config struct {
	DBHost        string
	DBPort        string
	DBUser        string
	DBPassword    string
	DBName        string
	TablePrefix   string
	NumProducts   int
	NumWorkers    int
	BatchSize     int
	VariableRatio int // 變體商品的百分比 (0-100)
}

// 產品資料結構
type Product struct {
	ID           int64
	Title        string
	Content      string
	Excerpt      string
	SKU          string
	RegularPrice float64
	SalePrice    float64
	Categories   []int
	Tags         []int
	IsVariable   bool
	Attributes   map[string][]string
}

// 全局變數
var config Config
var adjectives []string
var productTypes []string
var brands []string
var categories map[string][]string
var tags []string
var colorTerms []string
var sizeTerms []string
var productDescriptions []string
var shortDescriptions []string

// 初始化配置
func initConfig() {
	config = Config{
		DBHost:        "localhost",
		DBPort:        "3306",
		DBUser:        "root",
		DBPassword:    "root",
		DBName:        "wordpress",
		TablePrefix:   "wp_",
		NumProducts:   1000,
		NumWorkers:    runtime.NumCPU(), // 使用可用的 CPU 核心數
		BatchSize:     50,
		VariableRatio: 20, // 20%的商品將是變體商品
	}

	// 從環境變數讀取配置（如果有的話）
	if dbHost := os.Getenv("DB_HOST"); dbHost != "" {
		config.DBHost = dbHost
	}
	if dbPort := os.Getenv("DB_PORT"); dbPort != "" {
		config.DBPort = dbPort
	}
	if dbUser := os.Getenv("DB_USER"); dbUser != "" {
		config.DBUser = dbUser
	}
	if dbPass := os.Getenv("DB_PASSWORD"); dbPass != "" {
		config.DBPassword = dbPass
	}
	if dbName := os.Getenv("DB_NAME"); dbName != "" {
		config.DBName = dbName
	}
	if tablePrefix := os.Getenv("TABLE_PREFIX"); tablePrefix != "" {
		config.TablePrefix = tablePrefix
	}
	if numProducts := os.Getenv("NUM_PRODUCTS"); numProducts != "" {
		if n, err := strconv.Atoi(numProducts); err == nil {
			config.NumProducts = n
		}
	}
	if numWorkers := os.Getenv("NUM_WORKERS"); numWorkers != "" {
		if n, err := strconv.Atoi(numWorkers); err == nil {
			config.NumWorkers = n
		}
	}
}

// 初始化測試數據
func initTestData() {
	// 初始化形容詞
	adjectives = []string{"優質", "高級", "豪華", "經典", "時尚", "專業", "舒適", "實用", "創新", "耐用", "精美", "限量版", "全新", "熱銷", "獨特"}

	// 初始化產品類型
	productTypes = []string{"手機", "電腦", "平板", "耳機", "手錶", "相機", "電視", "音響", "冰箱", "洗衣機", "微波爐", "空調", "咖啡機", "吸塵器", "電風扇"}

	// 初始化品牌
	brands = []string{"蘋果", "三星", "華為", "小米", "索尼", "戴爾", "惠普", "聯想", "華碩", "飛利浦", "松下", "海爾", "美的", "博世", "LG"}

	// 初始化分類
	categories = map[string][]string{
		"電子產品": {"手機配件", "電腦周邊", "智能家居"},
		"家居用品": {"廚房用具", "衛浴用品", "臥室傢俱"},
		"服裝鞋帽": {"男士服裝", "女士服裝", "兒童服裝"},
		"食品飲料": {"零食", "飲料", "生鮮食品"},
		"美妝護膚": {"面部護理", "彩妝", "香水"},
		"母嬰用品": {"嬰兒食品", "嬰兒服裝", "玩具"},
		"體育戶外": {"健身器材", "戶外裝備", "運動服飾"},
		"書籍音像": {"文學小說", "教育教材", "音樂影視"},
		"汽車用品": {"汽車配件", "汽車裝飾", "汽車保養"},
		"辦公用品": {"文具", "辦公設備", "辦公傢俱"},
	}

	// 初始化標籤
	tags = []string{
		"熱銷", "促銷", "新品", "推薦", "限時特價",
		"品質保證", "超值", "限量", "經典", "暢銷",
		"進口", "國產", "高端", "實惠", "環保",
		"創新", "時尚", "簡約", "豪華", "實用",
	}

	// 初始化顏色
	colorTerms = []string{"紅色", "藍色", "綠色", "黑色", "白色", "黃色", "紫色", "灰色"}

	// 初始化尺寸
	sizeTerms = []string{"小", "中", "大", "XL", "XXL"}

	// 初始化商品描述
	productDescriptions = []string{
		"這款產品採用優質材料製造，堅固耐用，能夠承受日常使用的磨損。",
		"產品設計精巧，符合人體工學，使用舒適，操作便捷。",
		"獨特的設計風格，展現現代簡約美學，適合各種環境使用。",
		"高性能配置，運行流暢，滿足各種使用需求，提供卓越的用戶體驗。",
		"能效比高，省電環保，為您節省能源開支。",
		"多功能設計，滿足不同場景的使用需求，一機多用。",
		"操作簡單直觀，即使是初次使用也能快速上手。",
		"智能控制系統，可通過手機APP遠程操作，便捷又智能。",
		"採用最新技術，性能領先於同類產品，提供卓越體驗。",
		"完善的售後服務，購買後無後顧之憂，使用更放心。",
	}

	// 初始化商品短描述
	shortDescriptions = []string{
		"高品質產品，性價比極高，滿足您的日常需求。",
		"創新設計，卓越性能，提升您的使用體驗。",
		"精工打造，品質保證，是您理想的選擇。",
		"功能齊全，操作簡便，適合各年齡段用戶使用。",
		"時尚外觀，實用功能，為您的生活增添便利。",
		"優質選材，精心製作，帶來持久耐用的產品體驗。",
		"智能科技，人性化設計，讓生活更加便捷。",
		"強大性能，穩定可靠，滿足您的各種使用場景。",
		"經典款式，永不過時，值得您長期使用。",
		"超高性價比，同樣品質，更優惠的價格。",
	}
}

// 生成隨機字符串
func generateRandomString(length int) string {
	const charset = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
	result := make([]byte, length)
	for i := range result {
		result[i] = charset[rand.Intn(len(charset))]
	}
	return string(result)
}

// 生成產品名稱
func generateProductName() string {
	adj := adjectives[rand.Intn(len(adjectives))]
	brand := brands[rand.Intn(len(brands))]
	pType := productTypes[rand.Intn(len(productTypes))]
	randomStr := generateRandomString(4)

	return fmt.Sprintf("%s %s %s %s", adj, brand, pType, randomStr)
}

// 生成產品描述
func generateProductDescription() string {
	// 隨機選擇3-5段落
	numParagraphs := rand.Intn(3) + 3 // 3到5段

	// 避免重複
	selectedIndexes := make(map[int]bool)
	var description strings.Builder

	for i := 0; i < numParagraphs; i++ {
		for {
			idx := rand.Intn(len(productDescriptions))
			if !selectedIndexes[idx] {
				selectedIndexes[idx] = true
				description.WriteString(productDescriptions[idx])
				description.WriteString("\n\n")
				break
			}
		}
	}

	return description.String()
}

// 生成產品短描述
func generateShortDescription() string {
	return shortDescriptions[rand.Intn(len(shortDescriptions))]
}

// 生成SKU
func generateSKU() string {
	prefixes := []string{"WP", "SK", "PD", "IT", "GD"}
	prefix := prefixes[rand.Intn(len(prefixes))]
	return fmt.Sprintf("%s-%s", prefix, strings.ToUpper(generateRandomString(6)))
}

// 生成價格
func generatePrice() (float64, float64) {
	regularPrice := float64(rand.Intn(951)+50) + 0.99 // 50.99 - 1000.99
	var salePrice float64 = 0

	// 30%的機會有特價
	if rand.Intn(100) < 30 {
		discount := float64(rand.Intn(26)+70) / 100.0 // 70%-95%折扣
		salePrice = regularPrice * discount
		salePrice = float64(int(salePrice*100)) / 100 // 取兩位小數
	}

	return regularPrice, salePrice
}

// 連接資料庫
func connectToDB() (*sql.DB, error) {
	dsn := fmt.Sprintf(
		"%s:%s@tcp(%s:%s)/%s",
		config.DBUser,
		config.DBPassword,
		config.DBHost,
		config.DBPort,
		config.DBName,
	)

	db, err := sql.Open("mysql", dsn)
	if err != nil {
		return nil, err
	}

	db.SetMaxOpenConns(config.NumWorkers * 2)
	db.SetMaxIdleConns(config.NumWorkers)
	db.SetConnMaxLifetime(time.Minute * 5)

	return db, nil
}

// 確保產品分類存在並返回分類ID列表
func ensureProductCategories(db *sql.DB) ([]int, error) {
	var categoryIDs []int

	// 表名
	termsTable := config.TablePrefix + "terms"
	termTaxonomyTable := config.TablePrefix + "term_taxonomy"

	// 遍歷主分類
	for parent, children := range categories {
		// 檢查主分類是否存在
		var parentID int
		query := fmt.Sprintf("SELECT t.term_id FROM %s t INNER JOIN %s tt ON t.term_id = tt.term_id WHERE t.name = ? AND tt.taxonomy = 'product_cat'", termsTable, termTaxonomyTable)
		err := db.QueryRow(query, parent).Scan(&parentID)

		if err == sql.ErrNoRows {
			// 創建主分類
			result, err := db.Exec(fmt.Sprintf("INSERT INTO %s (name, slug) VALUES (?, ?)", termsTable), parent, strings.ToLower(strings.ReplaceAll(parent, " ", "-")))
			if err != nil {
				return nil, fmt.Errorf("創建主分類失敗: %v", err)
			}

			parentID64, err := result.LastInsertId()
			if err != nil {
				return nil, fmt.Errorf("獲取主分類ID失敗: %v", err)
			}
			parentID = int(parentID64)

			// 創建分類分類法
			_, err = db.Exec(
				fmt.Sprintf("INSERT INTO %s (term_id, taxonomy, description, parent) VALUES (?, ?, ?, ?)", termTaxonomyTable),
				parentID, "product_cat", "", 0,
			)
			if err != nil {
				return nil, fmt.Errorf("創建主分類分類法失敗: %v", err)
			}
		} else if err != nil {
			return nil, fmt.Errorf("檢查主分類失敗: %v", err)
		}

		categoryIDs = append(categoryIDs, parentID)

		// 處理子分類
		for _, child := range children {
			var childID int
			query := fmt.Sprintf("SELECT t.term_id FROM %s t INNER JOIN %s tt ON t.term_id = tt.term_id WHERE t.name = ? AND tt.taxonomy = 'product_cat' AND tt.parent = ?", termsTable, termTaxonomyTable)
			err := db.QueryRow(query, child, parentID).Scan(&childID)

			if err == sql.ErrNoRows {
				// 創建子分類
				result, err := db.Exec(fmt.Sprintf("INSERT INTO %s (name, slug) VALUES (?, ?)", termsTable), child, strings.ToLower(strings.ReplaceAll(child, " ", "-")))
				if err != nil {
					return nil, fmt.Errorf("創建子分類失敗: %v", err)
				}

				childID64, err := result.LastInsertId()
				if err != nil {
					return nil, fmt.Errorf("獲取子分類ID失敗: %v", err)
				}
				childID = int(childID64)

				// 創建分類分類法
				_, err = db.Exec(
					fmt.Sprintf("INSERT INTO %s (term_id, taxonomy, description, parent) VALUES (?, ?, ?, ?)", termTaxonomyTable),
					childID, "product_cat", "", parentID,
				)
				if err != nil {
					return nil, fmt.Errorf("創建子分類分類法失敗: %v", err)
				}
			} else if err != nil {
				return nil, fmt.Errorf("檢查子分類失敗: %v", err)
			}

			categoryIDs = append(categoryIDs, childID)
		}
	}

	return categoryIDs, nil
}

// 確保產品標籤存在並返回標籤ID列表
func ensureProductTags(db *sql.DB) ([]int, error) {
	var tagIDs []int

	// 表名
	termsTable := config.TablePrefix + "terms"
	termTaxonomyTable := config.TablePrefix + "term_taxonomy"

	// 遍歷所有標籤
	for _, tag := range tags {
		var tagID int
		query := fmt.Sprintf("SELECT t.term_id FROM %s t INNER JOIN %s tt ON t.term_id = tt.term_id WHERE t.name = ? AND tt.taxonomy = 'product_tag'", termsTable, termTaxonomyTable)
		err := db.QueryRow(query, tag).Scan(&tagID)

		if err == sql.ErrNoRows {
			// 創建標籤
			result, err := db.Exec(fmt.Sprintf("INSERT INTO %s (name, slug) VALUES (?, ?)", termsTable), tag, strings.ToLower(strings.ReplaceAll(tag, " ", "-")))
			if err != nil {
				return nil, fmt.Errorf("創建標籤失敗: %v", err)
			}

			tagID64, err := result.LastInsertId()
			if err != nil {
				return nil, fmt.Errorf("獲取標籤ID失敗: %v", err)
			}
			tagID = int(tagID64)

			// 創建標籤分類法
			_, err = db.Exec(
				fmt.Sprintf("INSERT INTO %s (term_id, taxonomy, description, parent) VALUES (?, ?, ?, ?)", termTaxonomyTable),
				tagID, "product_tag", "", 0,
			)
			if err != nil {
				return nil, fmt.Errorf("創建標籤分類法失敗: %v", err)
			}
		} else if err != nil {
			return nil, fmt.Errorf("檢查標籤失敗: %v", err)
		}

		tagIDs = append(tagIDs, tagID)
	}

	return tagIDs, nil
}

// 確保產品屬性存在
func ensureProductAttributes(db *sql.DB) error {
	// 表名
	termsTable := config.TablePrefix + "terms"
	termTaxonomyTable := config.TablePrefix + "term_taxonomy"

	// 處理顏色屬性
	for _, color := range colorTerms {
		var termID int
		query := fmt.Sprintf("SELECT t.term_id FROM %s t INNER JOIN %s tt ON t.term_id = tt.term_id WHERE t.name = ? AND tt.taxonomy = 'pa_color'", termsTable, termTaxonomyTable)
		err := db.QueryRow(query, color).Scan(&termID)

		if err == sql.ErrNoRows {
			// 創建顏色術語
			result, err := db.Exec(fmt.Sprintf("INSERT INTO %s (name, slug) VALUES (?, ?)", termsTable), color, strings.ToLower(strings.ReplaceAll(color, " ", "-")))
			if err != nil {
				return fmt.Errorf("創建顏色屬性失敗: %v", err)
			}

			termID64, err := result.LastInsertId()
			if err != nil {
				return fmt.Errorf("獲取顏色屬性ID失敗: %v", err)
			}
			termID = int(termID64)

			// 創建屬性分類法
			_, err = db.Exec(
				fmt.Sprintf("INSERT INTO %s (term_id, taxonomy, description, parent) VALUES (?, ?, ?, ?)", termTaxonomyTable),
				termID, "pa_color", "", 0,
			)
			if err != nil {
				return fmt.Errorf("創建顏色屬性分類法失敗: %v", err)
			}
		} else if err != nil {
			return fmt.Errorf("檢查顏色屬性失敗: %v", err)
		}
	}

	// 處理尺寸屬性
	for _, size := range sizeTerms {
		var termID int
		query := fmt.Sprintf("SELECT t.term_id FROM %s t INNER JOIN %s tt ON t.term_id = tt.term_id WHERE t.name = ? AND tt.taxonomy = 'pa_size'", termsTable, termTaxonomyTable)
		err := db.QueryRow(query, size).Scan(&termID)

		if err == sql.ErrNoRows {
			// 創建尺寸術語
			result, err := db.Exec(fmt.Sprintf("INSERT INTO %s (name, slug) VALUES (?, ?)", termsTable), size, strings.ToLower(strings.ReplaceAll(size, " ", "-")))
			if err != nil {
				return fmt.Errorf("創建尺寸屬性失敗: %v", err)
			}

			termID64, err := result.LastInsertId()
			if err != nil {
				return fmt.Errorf("獲取尺寸屬性ID失敗: %v", err)
			}
			termID = int(termID64)

			// 創建屬性分類法
			_, err = db.Exec(
				fmt.Sprintf("INSERT INTO %s (term_id, taxonomy, description, parent) VALUES (?, ?, ?, ?)", termTaxonomyTable),
				termID, "pa_size", "", 0,
			)
			if err != nil {
				return fmt.Errorf("創建尺寸屬性分類法失敗: %v", err)
			}
		} else if err != nil {
			return fmt.Errorf("檢查尺寸屬性失敗: %v", err)
		}
	}

	return nil
}

// 創建簡單產品
func createSimpleProduct(db *sql.DB, categoryIDs, tagIDs []int) (int64, error) {
	// 表名
	postsTable := config.TablePrefix + "posts"
	postmetaTable := config.TablePrefix + "postmeta"
	termRelationshipsTable := config.TablePrefix + "term_relationships"

	// 產品基本資訊
	title := generateProductName()
	content := generateProductDescription()
	excerpt := generateShortDescription()
	now := time.Now().Format("2006-01-02 15:04:05")
	postName := strings.ToLower(strings.ReplaceAll(title, " ", "-"))

	// 創建產品文章
	result, err := db.Exec(
		fmt.Sprintf("INSERT INTO %s (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, post_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", postsTable),
		1, now, now, content, title, excerpt, "publish", "open", "closed", postName, now, now, "product",
	)
	if err != nil {
		return 0, fmt.Errorf("創建產品失敗: %v", err)
	}

	productID, err := result.LastInsertId()
	if err != nil {
		return 0, fmt.Errorf("獲取產品ID失敗: %v", err)
	}

	// 設置產品類型
	var productTypeTermID int64
	var tmpID int64 // 臨時變數
	err = db.QueryRow(fmt.Sprintf("SELECT term_taxonomy_id FROM %s tt INNER JOIN %s t ON tt.term_id = t.term_id WHERE t.name = 'simple' AND tt.taxonomy = 'product_type'", config.TablePrefix+"term_taxonomy", config.TablePrefix+"terms")).Scan(&tmpID)

	if err != nil && err != sql.ErrNoRows {
		return productID, fmt.Errorf("獲取simple產品類型失敗: %v", err)
	} else if err == nil {
		// 產品類型存在，使用查詢到的ID
		productTypeTermID = tmpID
	} else if err == sql.ErrNoRows {
		// 創建產品類型術語
		res, err := db.Exec(fmt.Sprintf("INSERT INTO %s (name, slug) VALUES (?, ?)", config.TablePrefix+"terms"), "simple", "simple")
		if err != nil {
			return productID, fmt.Errorf("創建simple產品類型術語失敗: %v", err)
		}

		termID, err := res.LastInsertId()
		if err != nil {
			return productID, fmt.Errorf("獲取術語ID失敗: %v", err)
		}

		// 創建產品類型分類法
		res, err = db.Exec(
			fmt.Sprintf("INSERT INTO %s (term_id, taxonomy, description, parent) VALUES (?, ?, ?, ?)", config.TablePrefix+"term_taxonomy"),
			termID, "product_type", "", 0,
		)
		if err != nil {
			return productID, fmt.Errorf("創建產品類型分類法失敗: %v", err)
		}

		productTypeTermID, err = res.LastInsertId()
		if err != nil {
			return productID, fmt.Errorf("獲取分類法ID失敗: %v", err)
		}
	}

	// 關聯產品類型
	_, err = db.Exec(
		fmt.Sprintf("INSERT INTO %s (object_id, term_taxonomy_id, term_order) VALUES (?, ?, ?)", termRelationshipsTable),
		productID, productTypeTermID, 0,
	)
	if err != nil {
		return productID, fmt.Errorf("關聯產品類型失敗: %v", err)
	}

	// 隨機選擇1-3個分類
	numCats := rand.Intn(3) + 1
	if numCats > len(categoryIDs) {
		numCats = len(categoryIDs)
	}

	// 打亂分類ID數組並選取前numCats個
	rand.Shuffle(len(categoryIDs), func(i, j int) {
		categoryIDs[i], categoryIDs[j] = categoryIDs[j], categoryIDs[i]
	})

	for i := 0; i < numCats; i++ {
		_, err := db.Exec(
			fmt.Sprintf("INSERT INTO %s (object_id, term_taxonomy_id, term_order) VALUES (?, ?, ?)", termRelationshipsTable),
			productID, categoryIDs[i], 0,
		)
		if err != nil {
			return productID, fmt.Errorf("關聯產品分類失敗: %v", err)
		}
	}

	// 隨機選擇0-5個標籤
	numTags := rand.Intn(6)
	if numTags > 0 {
		if numTags > len(tagIDs) {
			numTags = len(tagIDs)
		}

		// 打亂標籤ID數組並選取前numTags個
		rand.Shuffle(len(tagIDs), func(i, j int) {
			tagIDs[i], tagIDs[j] = tagIDs[j], tagIDs[i]
		})

		for i := 0; i < numTags; i++ {
			_, err := db.Exec(
				fmt.Sprintf("INSERT INTO %s (object_id, term_taxonomy_id, term_order) VALUES (?, ?, ?)", termRelationshipsTable),
				productID, tagIDs[i], 0,
			)
			if err != nil {
				return productID, fmt.Errorf("關聯產品標籤失敗: %v", err)
			}
		}
	}

	// 設置產品屬性
	regularPrice, salePrice := generatePrice()
	sku := generateSKU()
	stockQuantity := rand.Intn(101) // 0-100
	stockStatus := "instock"
	if stockQuantity == 0 || rand.Intn(20) == 0 {
		stockStatus = "outofstock"
	}

	// 插入產品元數據
	metaValues := []struct {
		key   string
		value string
	}{
		{"_sku", sku},
		{"_regular_price", fmt.Sprintf("%.2f", regularPrice)},
		{"_price", fmt.Sprintf("%.2f", regularPrice)},
		{"_manage_stock", "yes"},
		{"_stock", strconv.Itoa(stockQuantity)},
		{"_stock_status", stockStatus},
		{"_weight", fmt.Sprintf("%.1f", float64(rand.Intn(50))/10.0)},
		{"_length", strconv.Itoa(rand.Intn(91) + 10)}, // 10-100
		{"_width", strconv.Itoa(rand.Intn(91) + 10)},  // 10-100
		{"_height", strconv.Itoa(rand.Intn(91) + 10)}, // 10-100
		{"_virtual", "no"},
		{"_downloadable", "no"},
		{"_sold_individually", func() string {
			if rand.Intn(10) == 0 {
				return "yes"
			}
			return "no"
		}()},
		{"_featured", func() string {
			if rand.Intn(10) == 0 {
				return "yes"
			}
			return "no"
		}()},
	}

	// 如果有特價，添加特價元數據
	if salePrice > 0 {
		metaValues = append(metaValues, struct {
			key   string
			value string
		}{"_sale_price", fmt.Sprintf("%.2f", salePrice)})
		metaValues = append(metaValues, struct {
			key   string
			value string
		}{"_price", fmt.Sprintf("%.2f", salePrice)})
	}

	// 插入所有元數據
	for _, meta := range metaValues {
		_, err := db.Exec(
			fmt.Sprintf("INSERT INTO %s (post_id, meta_key, meta_value) VALUES (?, ?, ?)", postmetaTable),
			productID, meta.key, meta.value,
		)
		if err != nil {
			return productID, fmt.Errorf("設置產品元數據失敗: %v", err)
		}
	}

	return productID, nil
}

// 主函數
func main() {
	// 初始化隨機數種子
	rand.Seed(time.Now().UnixNano())

	// 初始化配置
	initConfig()

	// 初始化測試數據
	initTestData()

	// 顯示配置信息
	fmt.Printf("使用 %d 個工作進程生成 %d 個產品\n", config.NumWorkers, config.NumProducts)

	// 連接資料庫
	db, err := connectToDB()
	if err != nil {
		log.Fatalf("無法連接資料庫: %v", err)
	}
	defer db.Close()

	// 測試資料庫連接
	if err := db.Ping(); err != nil {
		log.Fatalf("資料庫連接測試失敗: %v", err)
	}

	fmt.Println("成功連接到資料庫")

	// 確保產品分類存在
	fmt.Println("確保產品分類存在...")
	categoryIDs, err := ensureProductCategories(db)
	if err != nil {
		log.Fatalf("無法確保產品分類: %v", err)
	}

	// 確保產品標籤存在
	fmt.Println("確保產品標籤存在...")
	tagIDs, err := ensureProductTags(db)
	if err != nil {
		log.Fatalf("無法確保產品標籤: %v", err)
	}

	// 確保產品屬性存在
	fmt.Println("確保產品屬性存在...")
	if err := ensureProductAttributes(db); err != nil {
		log.Fatalf("無法確保產品屬性: %v", err)
	}

	// 開始計時
	startTime := time.Now()

	// 創建產品通道
	productChan := make(chan int, config.NumProducts)
	for i := 0; i < config.NumProducts; i++ {
		productChan <- i
	}
	close(productChan)

	// 創建等待組
	var wg sync.WaitGroup

	// 結果計數器
	productsCreated := 0
	var countMutex sync.Mutex

	// 創建工作進程
	for i := 0; i < config.NumWorkers; i++ {
		wg.Add(1)
		go func(workerID int) {
			defer wg.Done()

			// 每個工作進程單獨連接資料庫，避免並發問題
			workerDB, err := connectToDB()
			if err != nil {
				log.Printf("工作進程 %d 無法連接資料庫: %v", workerID, err)
				return
			}
			defer workerDB.Close()

			for range productChan {
				// 決定是否創建變體產品
				isVariable := rand.Intn(100) < config.VariableRatio

				var productID int64
				var err error

				if isVariable {
					// TODO: 實現變體產品創建
					productID, err = createSimpleProduct(workerDB, categoryIDs, tagIDs) // 暫時用簡單產品代替
				} else {
					productID, err = createSimpleProduct(workerDB, categoryIDs, tagIDs)
				}

				if err != nil {
					log.Printf("工作進程 %d 創建產品失敗: %v", workerID, err)
					continue
				}

				// 更新計數器
				countMutex.Lock()
				productsCreated++
				current := productsCreated
				countMutex.Unlock()

				if current%10 == 0 || current == config.NumProducts {
					log.Printf("已創建 %d/%d 個產品 (%.2f%%)", current, config.NumProducts, float64(current)/float64(config.NumProducts)*100)
				}
			}
		}(i)
	}

	// 等待所有工作進程完成
	wg.Wait()

	// 計算執行時間
	duration := time.Since(startTime)

	fmt.Printf("\n所有產品創建完成！\n")
	fmt.Printf("總共創建了 %d 個產品\n", productsCreated)
	fmt.Printf("執行時間: %.2f 秒\n", duration.Seconds())
	fmt.Printf("每秒產生 %.2f 個產品\n", float64(productsCreated)/duration.Seconds())
}
