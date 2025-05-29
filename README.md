
# EstateX AI Chatbot (Frontend + PHP Backend)

A modern, voice-enabled AI chatbot interface built for real estate platforms like Estatex.com. Combines a sleek frontend with a powerful PHP + GPT-4 backend to deliver intelligent, real-time support for property-related queries.

---

## 🧠 Features

- 🎤 Voice input (SpeechRecognition API)
- 🌙 Dark mode with theme persistence
- 🔍 Search through past chat messages
- 📤 Export chat history as `.txt`
- 🧼 Clear conversation functionality
- 🧠 AI backend using:
  - Local knowledge base (JSON)
  - SerpAPI for live web context
  - OpenAI GPT-4 for real estate Q&A

---

## 📂 Tech Stack

| Layer      | Tech Used                 |
|------------|---------------------------|
| Frontend   | HTML5, TailwindCSS, JS    |
| AI Backend | PHP, OpenAI GPT-4, SerpAPI|
| Storage    | JSON files (knowledge, logs) |

---

## 🚀 Getting Started

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/estatex-ai-chatbot.git
cd estatex-ai-chatbot
```

### 2. File Structure

```
estatex-ai-chatbot/
├── index.html                            # Frontend
├── chatbot_backend.php                   # PHP backend logic
├── estatex_chatbot_knowledge_phrased.json # Knowledge base
├── unanswered_questions.json             # Logged unanswered messages
├── logo.svg / logo2.svg                  # Brand assets
└── README.md
```

### 3. Setup Backend

- Ensure your server supports **PHP 7+**
- Host the project in a LAMP/XAMPP environment or live PHP hosting
- Replace API keys in `chatbot_backend.php`:
  - `$serpApiKey`
  - `$openaiApiKey`

---

## 🔐 Security Recommendations

- Avoid hardcoding API keys in production
- Use `.env` files or server environment variables
- Implement basic authentication if exposing the chatbot publicly

---

## 🧠 Knowledge Base Format

```json
[
  {
    "question": "what is estatex?",
    "answer": "Estatex is Pakistan's leading real estate platform offering B2B & B2C solutions..."
  },
  ...
]
```

---


## 📄 License

MIT License — use, modify, and share freely.

---

## 👨‍💻 Author

Developed by Kumail Raza
Contact: Kumailr952@gmail.com
