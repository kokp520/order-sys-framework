import http from 'k6/http';
import { sleep } from 'k6';

export const options = {
    vus: 100, // 100 個虛擬用戶
    duration: '30s', // 測試持續 30 秒
};

export default function () {
    http.get('http://localhost:8080/'); // 首頁
    http.get('http://localhost:8080/product/eggy%e5%b0%8f%e5%ae%85%e5%8c%85%ef%bd%9c%e5%af%b5%e7%89%a9%e5%b1%85%e5%ae%b6%e5%a4%96%e5%87%ba%e5%85%a9%e7%94%a8%e5%8c%85//'); // 產品頁面
    http.get('http://localhost:8080/checkout/'); // 結帳頁面
    sleep(1); // 模擬用戶停留
}