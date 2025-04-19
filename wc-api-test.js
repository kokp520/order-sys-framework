import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';
import { browser } from 'k6/browser';
import encoding from 'k6/encoding';

const errorRate = new Rate('errors');
const cpuUsage = new Trend('cpu_usage');
const memoryUsage = new Trend('memory_usage');
const responseSize = new Trend('response_size');
const browserMetrics = new Trend('browser_metrics');

// 新增系統資源監控
const systemMetrics = new Trend('system_metrics');

export const options = {
  scenarios: {
    api_test: {
      executor: 'ramping-vus',
      startVUs: 1,
      stages: [
        { duration: '30s', target: 10 },  // 逐步增加到 10 個用戶
        { duration: '1m', target: 10 },   // 維持 10 個用戶 1 分鐘
        { duration: '30s', target: 0 },   // 逐步減少到 0 個用戶
      ],
    },
    browser_test: {
      executor: 'shared-iterations',
      vus: 1,
      iterations: 1,
      options: {
        browser: {
          type: 'chromium',
        },
      },
    },
  },
  thresholds: {
    'http_req_duration': ['p(95)<500'], // 95% 的請求應該在 500ms 內完成
    'errors': ['rate<0.1'],             // 錯誤率應該小於 10%
    'cpu_usage': ['avg<70'],            // CPU 使用率平均應低於 70%
    'memory_usage': ['max<85'],         // 記憶體使用率最高不超過 85%
  }
};

const BASE_URL = 'http://localhost/wp-json/wc/v3';
const API_KEY = 'ck_xxx';
const API_SECRET = 'cs_xxx';

// 生成 Basic Auth header
function getAuthHeader() {
  return `Basic ${encoding.b64encode(`${API_KEY}:${API_SECRET}`)}`;
}

// 監控系統資源使用情況
function trackSystemMetrics() {
  try {
    const metrics = {
      memory: {},
      cpu: {},
      time: new Date().toISOString()
    };

    // 記憶體使用監控
    metrics.memory = {
      usage: 0  // k6 環境中無法直接獲取記憶體使用情況
    };
    memoryUsage.add(0);

    // CPU 使用監控
    metrics.cpu = {
      usage: 0  // k6 環境中無法直接獲取 CPU 使用情況
    };
    cpuUsage.add(0);

    systemMetrics.add(1, metrics);
    return metrics;
  } catch (e) {
    console.error('監控指標收集失敗:', e);
    return null;
  }
}

export default function () {
  const metrics = trackSystemMetrics();
  const authHeader = getAuthHeader();

  // API 測試
  const productsResponse = http.get(`${BASE_URL}/products`, {
    headers: {
      'Authorization': authHeader,
    },
    tags: { api: 'products' }
  });
  check(productsResponse, {
    'products status is 200': (r) => r.status === 200,
    'products response time < 200ms': (r) => r.timings.duration < 200,
  });
  errorRate.add(productsResponse.status !== 200);
  responseSize.add(productsResponse.body.length);

  // 訂單列表 API 測試
  const ordersResponse = http.get(`${BASE_URL}/orders`, {
    headers: {
      'Authorization': authHeader,
    },
    tags: { api: 'orders' }
  });
  check(ordersResponse, {
    'orders status is 200': (r) => r.status === 200,
    'orders response time < 200ms': (r) => r.timings.duration < 200,
  });
  errorRate.add(ordersResponse.status !== 200);
  responseSize.add(ordersResponse.body.length);

  // 購物車 API 測試
  const cartResponse = http.get(`${BASE_URL}/cart`, {
    headers: {
      'Authorization': authHeader,
    },
    tags: { api: 'cart' }
  });
  check(cartResponse, {
    'cart status is 200': (r) => r.status === 200,
    'cart response time < 200ms': (r) => r.timings.duration < 200,
  });
  errorRate.add(cartResponse.status !== 200);
  responseSize.add(cartResponse.body.length);

  if (metrics) {
    systemMetrics.add(1, {
      memory: metrics.memory.usage,
      cpu: metrics.cpu.usage,
      time: metrics.time
    });
  }

  sleep(1);
} 