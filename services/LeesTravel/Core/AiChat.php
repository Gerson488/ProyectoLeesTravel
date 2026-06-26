<?php
require_once(__DIR__ . '/../Config/Setting.php');
require_once(__DIR__ . '/../Core/Promotion.php');
require_once(__DIR__ . '/../Core/Trips.php');
require_once(__DIR__ . '/../Core/Itinerary.php');
require_once(__DIR__ . '/Quote.php');

class AiChat {
    private $apiKey;
    private $apiUrl;
    private $baseUrl;
    private $db;

    public function __construct() {
        $this->apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $this->apiKey;
        $this->baseUrl = $_ENV['APP_BASE_URL'] ?? '';
        
        $setting = new Setting();
        $this->db = $setting->getConnection();
    }

    private function getFullContext() {
        $promoManager = new MarketingPromo();
        $tripsManager = new Trips();
        $itineraryManager = new Itinerario();

        $promos = $promoManager->getPromotionChatIA() ?? [];
        $trips = $tripsManager->getTripsChatIA() ?? [];
        $itineraries = $itineraryManager->getItineraryChatIA() ?? [];

        return json_encode([
            "catalogo_viajes_base" => $trips,
            "promociones_especiales" => $promos,
            "itinerarios_detallados" => $itineraries
        ]);
    }

    public function getResponse($chatHistory) {
        $fullData = $this->getFullContext();
        
        $systemPrompt = "Eres 'LeesTravel AI', el asistente de ventas experto exclusivo de la agencia Lees Travel Cruises.
        
        TONO Y ESTILO:
        - Amable, entusiasta y náutico ('¡Bienvenido a bordo, Capitán!').
        - RESPUESTAS CORTAS Y DIRECTAS. Evita textos largos e interminables.
        
        REGLA 1: ENFOQUE ESTRICTO Y CERO OFF-TOPIC:
        - Eres UN ASESOR DE CRUCEROS, NO una IA genérica. NUNCA digas que sabes programar, resolver matemáticas o hacer tareas.
        - Si te preguntan sobre programación, recetas, chistes o CUALQUIER tema que no sea cruceros o Lees Travel, DEBES NEGARTE amablemente y ofrecer un viaje.

        REGLAS DE PRECISIÓN Y FORMATO:
        2. Solo recomienda cruceros del CONTEXTO. No inventes destinos ni precios.
        3. IMPORTANTE: Formatea TODA tu respuesta usando etiquetas HTML válidas (<b>, <br>, <ul>, <li>). NUNCA uses Markdown como asteriscos (**).

        4. REGLA PARA LISTAR CRUCEROS: Si recomiendas un viaje o promoción, usa ÚNICAMENTE este HTML exacto:
        <div class='card-ia'>
          <img src='{$this->baseUrl}[Trip_Photo_o_Image_Banner]' class='card-img' alt='[Destination_Name_o_Title_Offer]'>
          <div class='card-body-ia'>
            <span class='badge-price'>$[Price_o_Special_Price_USD] USD</span>
            <h3>[Destination_Name_o_Title_Offer]</h3>
            <p class='promo-title'>Barco: [Ship_Name]</p>
            <p class='validity'>⏳ Expira/Viaje: [Expiration_Date_o_End_Date]</p>
            <a href='#cotizar' class='btn-ia btn-cotizar-ia' data-destino='[Destination_Name_o_Title_Offer]' data-fecha='[Start_Date_o_Expiration_Date]'>¡Lo quiero ahora!</a>
          </div>
        </div>

        5. REGLA PARA ITINERARIOS: Si piden el itinerario de un viaje, busca en 'itinerarios_detallados' y usa ÚNICAMENTE este HTML exacto:
        <div class='card-ia'>
          <div class='card-body-ia'>
            <h3>🗺️ Itinerario: [Destination_Name]</h3>
            <ul class='itinerary-list'>
              <li><b>Día [Day_Number] - [Port_of_Call]:</b> [Activity_Description]</li>
            </ul>
          </div>
        </div>

        6. REGLA DE ASESOR HUMANO Y HORARIOS: Si el usuario pide hablar con un humano, contactar por WhatsApp o pide un número:
        - Informa nuestro horario: Lunes a Viernes de 8:00 AM a 6:00 PM, Sábados de 8:00 AM a 12:00 PM (Domingos cerrado).
        - Pídele que te deje su Nombre, Correo y Teléfono en el chat.
        - DEBES mostrar obligatoriamente este botón HTML para ir a WhatsApp:
        <br>
        <a href='https://wa.me/51976223450' target='_blank' class='btn-ia' style='background-color: #25D366; color: white; margin-top: 10px; display: inline-block;'>
          💬 Chatear con un Asesor
        </a>

        7. REGLA DE CAPTURA DE LEADS (MUY IMPORTANTE):
        Si en la conversación el usuario te proporciona explícitamente su NOMBRE, CORREO y TELÉFONO, DEBES:
        - Agradecerle y confirmarle que un Oficial de Viaje de Lees Travel lo contactará muy pronto.
        - OBLIGATORIAMENTE incluir al final de tu texto EXACTAMENTE esta etiqueta oculta reemplazando los datos (intenta deducir el destino del que hablaban):
        [LEAD_DATA|{\"fullName\":\"[Nombre]\", \"email\":\"[Correo]\", \"phone\":\"[Teléfono]\", \"destination\":\"[Destino]\"}]

        REGLA DE AMBIGÜEDAD: Si el usuario menciona un destino que tiene múltiples viajes disponibles (por ejemplo 'Caribe'), fíjate en las fechas ('Start_Date') proporcionadas en el contexto para identificar a qué viaje específico se refiere. Si el usuario no especifica la fecha, pregúntale: '¿Te refieres al viaje del 2025 o al del 2026?' antes de mostrar el itinerario.

        CONTEXTO ACTUAL DE LA BASE DE DATOS: " . $fullData;
        
        $data = [
            "system_instruction" => ["parts" => [["text" => $systemPrompt]]],
            "contents" => $chatHistory,
            "generationConfig" => [
                "temperature" => 0.2,
                "maxOutputTokens" => 900
            ]
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $response = curl_exec($ch);
        $decoded = json_decode($response, true);
        curl_close($ch);

        $textResponse = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? "Lo siento Capitán, hay una tormenta en las comunicaciones. Intenta de nuevo.";

        if (preg_match('/\[LEAD_DATA\|(.*?)\]/', $textResponse, $matches)) {
            $jsonData = $matches[1];
            $leadData = json_decode($jsonData, true);
            
            if ($leadData) {
                $quoteBE = new QuoteBE(
                    $leadData['fullName'] ?? 'Cliente de Chat IA',
                    $leadData['email'] ?? '',
                    '',
                    $leadData['phone'] ?? '',
                    $leadData['destination'] ?? 'Consulta General (Vía Chatbot IA)',
                    date('Y-m-d'),
                    '1',
                    'No especificado',
                    '¡Atención! Este prospecto dejó sus datos conversando directamente con LeesTravel AI.'
                );
                
                $quoteManager = new Quote();
                $quoteManager->saveQuoteToDB($quoteBE);
            }
            
            $textResponse = preg_replace('/\[LEAD_DATA\|.*?\]/', '', $textResponse);
        }

        return trim($textResponse);
    }
}