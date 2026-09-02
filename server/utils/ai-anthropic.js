const Anthropic = require('@anthropic-ai/sdk');

// Initialize Anthropic client
const anthropic = new Anthropic({
    apiKey: process.env.ANTHROPIC_API_KEY,
});

// Language-specific system prompts for Daisho Chemical
const systemPrompts = {
    en: `You are a helpful AI assistant for Daisho Chemical GLOBAL, a leading chemical company. 
You help customers with product inquiries, technical support, and general information about our chemical products and services. 
Be professional, knowledgeable, and helpful. Keep responses concise and clear.`,

    ja: `あなたは大正化学グローバルの役に立つAIアシスタントです。大正化学は大手化学会社です。
製品に関するお問い合わせ、技術サポート、化学製品やサービスに関する一般的な情報についてお客様をサポートします。
プロフェッショナルで知識豊富、そして親切に対応してください。回答は簡潔で明確にしてください。`,

    // Add other languages as needed...
};

async function generateAIResponse(text, lang = 'en') {
    try {
        const systemPrompt = systemPrompts[lang] || systemPrompts.en;
        const languagePrefix = `[${lang.toUpperCase()}]`;
        const fullPrompt = `${languagePrefix} ${text}`;

        const response = await anthropic.messages.create({
            model: 'claude-3-sonnet-20240229',
            max_tokens: 500,
            system: systemPrompt,
            messages: [
                {
                    role: 'user',
                    content: fullPrompt
                }
            ]
        });

        return response.content[0].text;

    } catch (error) {
        console.error('Error generating AI response:', error);
        throw new Error('AI service temporarily unavailable');
    }
}

module.exports = {
    generateAIResponse
};