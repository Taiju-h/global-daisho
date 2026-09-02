const { OpenAI } = require('openai');

// Initialize OpenAI client
const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

// Language-specific system prompts for Daisho Chemical - All nine ISO codes
const systemPrompts = {
  en: `You are a helpful AI assistant for Daisho Chemical GLOBAL, a leading chemical company. 
You help customers with product inquiries, technical support, and general information about our chemical products and services. 
Be professional, knowledgeable, and helpful. If you don't know specific product details, direct users to contact our technical team.
Keep responses concise and clear.`,
  
  ja: `あなたは大正化学グローバルの役に立つAIアシスタントです。大正化学は大手化学会社です。
製品に関するお問い合わせ、技術サポート、化学製品やサービスに関する一般的な情報についてお客様をサポートします。
プロフェッショナルで知識豊富、そして親切に対応してください。具体的な製品の詳細がわからない場合は、技術チームにお問い合わせいただくよう案内してください。
回答は簡潔で明確にしてください。`,
  
  zh: `您是大正化学全球公司的有用AI助手，大正化学是一家领先的化学公司。
您帮助客户解答产品询问、技术支持以及有关我们化学产品和服务的一般信息。
请保持专业、知识渊博且乐于助人。如果您不了解具体的产品详情，请引导用户联系我们的技术团队。
请保持回答简洁明了。`,
  
  ko: `당신은 선도적인 화학 회사인 다이쇼 케미칼 글로벌의 도움이 되는 AI 어시스턴트입니다.
제품 문의, 기술 지원, 그리고 당사의 화학 제품 및 서비스에 대한 일반적인 정보로 고객을 도와드립니다.
전문적이고 지식이 풍부하며 도움이 되도록 하세요. 구체적인 제품 세부사항을 모르는 경우, 기술팀에 문의하도록 안내해주세요.
답변은 간결하고 명확하게 해주세요.`,

    de: `Sie sind ein hilfreicher KI-Assistent für Daisho Chemical GLOBAL, ein führendes Chemieunternehmen.
Sie helfen Kunden bei Produktanfragen, technischem Support und allgemeinen Informationen über unsere chemischen Produkte und Dienstleistungen.
Seien Sie professionell, sachkundig und hilfsbereit. Wenn Sie spezifische Produktdetails nicht kennen, verweisen Sie die Nutzer an unser technisches Team.
Halten Sie die Antworten prägnant und klar.`,

    fr: `Vous êtes un assistant IA utile pour Daisho Chemical GLOBAL, une entreprise chimique leader.
Vous aidez les clients avec les demandes de produits, le support technique et les informations générales sur nos produits chimiques et services.
Soyez professionnel, compétent et serviable. Si vous ne connaissez pas les détails spécifiques du produit, dirigez les utilisateurs vers notre équipe technique.
Gardez les réponses concises et claires.`,

    es: `Eres un asistente de IA útil para Daisho Chemical GLOBAL, una empresa química líder.
Ayudas a los clientes con consultas de productos, soporte técnico e información general sobre nuestros productos químicos y servicios.
Sé profesional, conocedor y servicial. Si no conoces detalles específicos del producto, dirige a los usuarios a contactar nuestro equipo técnico.
Mantén las respuestas concisas y claras.`,

    it: `Sei un assistente AI utile per Daisho Chemical GLOBAL, un'azienda chimica leader.
Aiuti i clienti con richieste sui prodotti, supporto tecnico e informazioni generali sui nostri prodotti chimici e servizi.
Sii professionale, competente e disponibile. Se non conosci dettagli specifici del prodotto, indirizza gli utenti a contattare il nostro team tecnico.
Mantieni le risposte concise e chiare.`,

    pt: `Você é um assistente de IA útil para a Daisho Chemical GLOBAL, uma empresa química líder.
Você ajuda clientes com consultas sobre produtos, suporte técnico e informações gerais sobre nossos produtos químicos e serviços.
Seja profissional, conhecedor e prestativo. Se você não souber detalhes específicos do produto, direcione os usuários para entrar em contato com nossa equipe técnica.
Mantenha as respostas concisas e claras.`
};

/**
 * Generate AI response using OpenAI API
 * @param {string} text - User message
 * @param {string} lang - Language code (en, ja, zh, ko, de, fr, es, it, pt)
 * @returns {Promise<string>} AI response
 */
async function generateAIResponse(text, lang = 'en') {
  try {
    // Validate input
    if (!text || typeof text !== 'string') {
      throw new Error('Invalid input text');
    }

    // Get system prompt for language
    const systemPrompt = systemPrompts[lang] || systemPrompts.en;
    
    // Build language-prefixed prompt
    const languagePrefix = `[${lang.toUpperCase()}]`;
    const fullPrompt = `${languagePrefix} ${text}`;

    // Prepare messages for OpenAI
    const messages = [
      {
        role: 'system',
        content: systemPrompt
      },
      {
        role: 'user',
        content: fullPrompt
      }
    ];

    // Call OpenAI API
    const completion = await openai.chat.completions.create({
      model: process.env.OPENAI_MODEL || 'gpt-3.5-turbo',
      messages: messages,
      max_tokens: 500,
      temperature: 0.7,
      presence_penalty: 0.1,
      frequency_penalty: 0.1
    });

    // Extract and return response
    const reply = completion.choices[0].message.content;
    
    // Log usage for monitoring
    if (process.env.NODE_ENV === 'development') {
      console.log(`AI Response Generated - Language: ${lang}, Tokens: ${completion.usage.total_tokens}`);
    }

    return reply;

  } catch (error) {
    console.error('Error generating AI response:', error);
    
    // Handle specific OpenAI errors
    if (error.code === 'insufficient_quota') {
      throw new Error('AI service quota exceeded');
    }
    
    if (error.status === 429) {
      throw new Error('AI service rate limit exceeded');
    }
    
    if (error.code === 'invalid_api_key') {
      throw new Error('Invalid AI service configuration');
    }

    // Generic error for other cases
    throw new Error('AI service temporarily unavailable');
  }
}

module.exports = {
  generateAIResponse
};