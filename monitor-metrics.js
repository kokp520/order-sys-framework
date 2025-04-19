const fs = require('fs');
const path = require('path');

class SystemMonitor {
    constructor() {
        this.metricsPath = path.join(__dirname, 'metrics');
        this.ensureMetricsDirectory();
        this.metrics = {
            cpu: [],
            memory: [],
            responseTime: [],
            errorRate: []
        };
    }

    ensureMetricsDirectory() {
        if (!fs.existsSync(this.metricsPath)) {
            fs.mkdirSync(this.metricsPath);
        }
    }

    collectMetrics(data) {
        const timestamp = new Date().toISOString();
        this.metrics.cpu.push({ timestamp, value: data.cpu });
        this.metrics.memory.push({ timestamp, value: data.memory });
        this.metrics.responseTime.push({ timestamp, value: data.responseTime });
        this.metrics.errorRate.push({ timestamp, value: data.errorRate });

        // 寫入檔案
        this.writeMetrics();
    }

    writeMetrics() {
        const filename = path.join(this.metricsPath, `metrics-${new Date().toISOString()}.json`);
        fs.writeFileSync(filename, JSON.stringify(this.metrics, null, 2));
    }

    analyzeMetrics() {
        const analysis = {
            cpu: this.analyzeMetricSet(this.metrics.cpu),
            memory: this.analyzeMetricSet(this.metrics.memory),
            responseTime: this.analyzeMetricSet(this.metrics.responseTime),
            errorRate: this.analyzeMetricSet(this.metrics.errorRate)
        };

        return analysis;
    }

    analyzeMetricSet(metricSet) {
        if (metricSet.length === 0) return null;

        const values = metricSet.map(m => m.value);
        return {
            min: Math.min(...values),
            max: Math.max(...values),
            avg: values.reduce((a, b) => a + b, 0) / values.length,
            p95: this.calculatePercentile(values, 95),
            p99: this.calculatePercentile(values, 99)
        };
    }

    calculatePercentile(values, p) {
        const sorted = [...values].sort((a, b) => a - b);
        const pos = (sorted.length - 1) * p / 100;
        const base = Math.floor(pos);
        const rest = pos - base;
        if (sorted[base + 1] !== undefined) {
            return sorted[base] + rest * (sorted[base + 1] - sorted[base]);
        } else {
            return sorted[base];
        }
    }

    generateReport() {
        const analysis = this.analyzeMetrics();
        const report = {
            timestamp: new Date().toISOString(),
            summary: {
                cpu: {
                    ...analysis.cpu,
                    status: analysis.cpu.avg < 70 ? '正常' : '警告'
                },
                memory: {
                    ...analysis.memory,
                    status: analysis.memory.max < 85 ? '正常' : '警告'
                },
                responseTime: {
                    ...analysis.responseTime,
                    status: analysis.responseTime.p95 < 500 ? '正常' : '警告'
                },
                errorRate: {
                    ...analysis.errorRate,
                    status: analysis.errorRate.avg < 0.1 ? '正常' : '警告'
                }
            },
            recommendations: this.generateRecommendations(analysis)
        };

        // 寫入報告
        const reportPath = path.join(this.metricsPath, `report-${new Date().toISOString()}.json`);
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        return report;
    }

    generateRecommendations(analysis) {
        const recommendations = [];

        if (analysis.cpu.avg >= 70) {
            recommendations.push({
                type: 'CPU',
                severity: 'high',
                message: 'CPU 使用率過高，建議：',
                actions: [
                    '檢查並優化資料庫查詢',
                    '考慮增加快取層',
                    '評估是否需要增加服務器資源'
                ]
            });
        }

        if (analysis.memory.max >= 85) {
            recommendations.push({
                type: 'Memory',
                severity: 'high',
                message: '記憶體使用率接近上限，建議：',
                actions: [
                    '檢查記憶體洩漏',
                    '優化記憶體使用',
                    '考慮增加記憶體容量'
                ]
            });
        }

        if (analysis.responseTime.p95 >= 500) {
            recommendations.push({
                type: 'Performance',
                severity: 'medium',
                message: '響應時間過長，建議：',
                actions: [
                    '實作 API 快取',
                    '優化資料庫索引',
                    '檢查慢查詢'
                ]
            });
        }

        if (analysis.errorRate.avg >= 0.1) {
            recommendations.push({
                type: 'Reliability',
                severity: 'high',
                message: '錯誤率過高，建議：',
                actions: [
                    '檢查錯誤日誌',
                    '實作錯誤重試機制',
                    '增加錯誤監控告警'
                ]
            });
        }

        return recommendations;
    }
}

// 使用範例
const monitor = new SystemMonitor();

// 模擬收集指標
setInterval(() => {
    monitor.collectMetrics({
        cpu: Math.random() * 100,
        memory: Math.random() * 100,
        responseTime: Math.random() * 1000,
        errorRate: Math.random() * 0.2
    });
}, 5000);

// 每小時產生一次報告
setInterval(() => {
    const report = monitor.generateReport();
    console.log('系統效能報告已生成：', report);
}, 3600000);

// 監聽程序結束
process.on('SIGINT', () => {
    console.log('生成最終報告...');
    monitor.generateReport();
    process.exit();
}); 