const express = require('express');
const {generateAIResponse} = require('../utils/ai');
const router = express.Router();

// Rate limiting store (in production, use Redis or similar)
const rateLimitStore = new Map();

// Rate limiting middleware
const rateLimit = (req, res, next) => {
    const clientIP = req.ip || req.connection.remoteAddress;
    const now = Date.now();
    const windowMs = parseInt(process.env.RATE_LIMIT_WINDOW) || 60000; // 1 minute
    const maxRequests = parseInt(process.env.RATE_LIMIT_REQUESTS) || 10;

    if (!rateLimitStore.has(clientIP)) {
        rateLimitStore.set(clientIP, {requests: 1, windowStart: now});
        return next();
    }

    const clientData = rateLimitStore.get(clientIP);

    // Reset window if expired
    if (now - clientData.windowStart > windowMs) {
        rateLimitStore.set(clientIP, {requests: 1, windowStart: now});
        return next();
    }

    // Check if exceeded limit
    if (clientData.requests >= maxRequests) {
        return res.status(429).json({
            error: 'Rate limit exceeded',
            retryAfter: Math.ceil((windowMs - (now - clientData.windowStart)) / 1000)
        });
    }

    // Increment request count
    clientData.requests++;
    next();
};

// Multi-language system messages for Daisho Chemical
const systemMessages = {
    en: `You are a helpful AI assistant for Daisho Chemical GLOBAL, a leading chemical company. 
You help customers with product inquiries, technical support, and general information about our chemical products and services. 
Be professional, knowledgeable, and helpful. If you don't know specific product details, direct users to contact our technical team.`,

    ja: `あなたは大正化学���ローバルの役に立つAIアシスタントです。大正化学は大手化学会社です。
製品に関するお問い合わせ、技術サポート、化学製品やサービスに関する一般的な情報についてお客様をサポートします。
プロフェッショナルで知識豊富、そして親切に対応してください。具体的な製品の詳細がわからない場合は、技術チームにお問い合わせいただくよう案内してください。`,

    zh: `您是大正化学全球公司的有用AI助手，大正化学是一家领先的化学公司。
您帮助客户解答产品询问、技术支持以及有关我们化学产品和服务的一般信息。
请保持专业、知识渊博且乐于助人。如果您不了解具体的产品详情，请引导用户联系我们的技术团队。`,

    ko: `당신은 선도적인 화학 회사인 다이쇼 케미칼 글로벌의 도움이 되는 AI 어시스턴트입니다.
제품 문의, 기술 지원, 그리고 당사의 화학 제품 및 서비스에 대한 일반적인 정보로 고객을 도와드립니다.
전문적이고 지식이 풍부하며 도움이 되도록 하세요. 구체적인 제품 세부사항을 모르는 경우, 기술팀에 문의하도록 안내해주세요.`
};

// Main chat endpoint - POST /
router.post('/', rateLimit, async (req, res) => {
    try {
        // Extract message and language from request body
        const {message, lang = 'en'} = req.body;

        // Validate input
        if (!message || typeof message !== 'string') {
            return res.status(400).json({
                error: 'Message is required and must be a string'
            });
        }

        // Validate language
        const supportedLanguages = ['en', 'ja', 'zh', 'ko', 'de', 'fr', 'es', 'it', 'pt'];
        const language = supportedLanguages.includes(lang) ? lang : 'en';

        // Generate AI response
        const reply = await generateAIResponse(message, language);

        // Return response
        res.json({
            reply,
            language,
            timestamp: new Date().toISOString()
        });

    } catch (error) {
        console.error('Error in chat endpoint:', error);

        // Handle different types of errors
        if (error.message.includes('quota')) {
            return res.status(503).json({error: 'Service temporarily unavailable'});
        }

        if (error.message.includes('rate limit')) {
            return res.status(429).json({error: 'Service rate limit exceeded'});
        }

        if (error.message.includes('configuration')) {
            return res.status(500).json({error: 'Service configuration error'});
        }

        res.status(500).json({error: 'Internal server error'});
    }
});

// Health check endpoint
router.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        timestamp: new Date().toISOString(),
        service: 'Daisho Chemical AI Chat'
    });
});

// Get supported languages
router.get('/languages', (req, res) => {
    res.json({
        languages: ['en', 'ja', 'zh', 'ko', 'de', 'fr', 'es', 'it', 'pt'],
        default: 'en'
    });
});

module.exports = router;