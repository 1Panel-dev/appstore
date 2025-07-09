# Typesense

**Typesense** is a fast, typo-tolerant search engine for building delightful search experiences.

## Features:

- **Typo Tolerance:** Handles typographical errors elegantly, out-of-the-box.
- **Simple and Delightful:** Simple to set-up, integrate with, operate and scale.
- **⚡ Blazing Fast:** Built in C++. Meticulously architected from the ground-up for low-latency (<50ms) instant searches.
- **Tunable Ranking:** Easy to tailor your search results to perfection.
- **Sorting:** Dynamically sort results based on a particular field at query time (helpful for features like "Sort by Price (asc)").
- **Faceting & Filtering:** Drill down and refine results.
- **Grouping & Distinct:** Group similar results together to show more variety.
- **Federated Search:** Search across multiple collections (indices) in a single HTTP request.
- **Geo Search:** Search and sort by results around a latitude/longitude or within a bounding box.
- **Vector Search:** Index embeddings from your machine learning models in Typesense and do a nearest-neighbor search. Can be used to build similarity search, semantic search, visual search, recommendations, etc.
- **Semantic / Hybrid Search:** Automatically generate embeddings from within Typesense using built-in models like S-BERT, E-5, etc or use OpenAI, PaLM API, etc, for both queries and indexed data. This allows you to send JSON data into Typesense and build an out-of-the-box semantic search + keyword search experience.
- **Conversational Search (Built-in RAG):** Send questions to Typesense and have the response be a fully-formed sentence, based on the data you've indexed in Typesense. Think ChatGPT, but over your own data.
- **Natural Language Search:** LLM-powered intent detection & query understanding, that converts any free-form natural language phrases into structured filters, sorts and queries.
- **Image Search:** Search through images using text descriptions of their contents, or perform similarity searches, using the CLIP model.
- **Voice Search:** Capture and send query via voice recordings - Typesense will transcribe (via Whisper model) and provide search results.
- **Scoped API Keys:** Generate API keys that only allow access to certain records, for multi-tenant applications.
- **JOINs:** Connect one or more collections via common reference fields and join them during query time. This allows you to model SQL-like relationships elegantly.
- **Synonyms:** Define words as equivalents of each other, so searching for a word will also return results for the synonyms defined.
- **Curation & Merchandizing:** Boost particular records to a fixed position in the search results, to feature them.
- **Raft-based Clustering:** Setup a distributed cluster that is highly available.
- **Seamless Version Upgrades:** As new versions of Typesense come out, upgrading is as simple as swapping out the binary and restarting Typesense.
- **No Runtime Dependencies:** Typesense is a single binary that you can run locally or in production with a single command.
