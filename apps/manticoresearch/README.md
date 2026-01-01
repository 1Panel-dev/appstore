# 介绍

Manticore Search 是一个高性能、多存储的数据库，专为搜索和分析而设计，提供极速的全文搜索、实时索引以及向量搜索和列存储等高级功能，以实现高效的数据分析。它既能处理小型数据集，也能应对大型数据集，提供无缝的可扩展性和强大的洞察力，适用于现代应用。

> 警告：Manticore Search 没有用户认证功能，未被反向代理并增加用户认证前严禁暴露在公网！

## 特点

### 强大且快速的全文搜索，适用于小型和大型数据集

* [查询自动补全](Searching/Autocomplete.md)
* [模糊搜索](Searching/Spell_correction.md#Fuzzy-Search)
* 超过 20 种 [全文操作符](https://play.manticoresearch.com/fulltextintro/)<!--{target="_blank"}--> 和超过 20 种排名因素
* [自定义排名](Searching/Sorting_and_ranking.md#Ranking-overview)
* [词干提取](Creating_a_table/NLP_and_tokenization/Morphology.md)
* [词形还原](Creating_a_table/NLP_and_tokenization/Morphology.md)
* [停用词](Creating_a_table/NLP_and_tokenization/Ignoring_stop-words.md)
* [同义词](Creating_a_table/NLP_and_tokenization/Exceptions.md)
* [词形变化](Creating_a_table/NLP_and_tokenization/Wordforms.md)
* [字符和词级别的高级分词](Creating_a_table/NLP_and_tokenization/Low-level_tokenization.md)
* [准确的中文分词](Creating_a_table/NLP_and_tokenization/Languages_with_continuous_scripts.md)
* [文本高亮](Searching/Highlighting.md)

## 可能的应用

Manticore Search 多功能，可应用于多种场景，包括：

- **全文搜索**：
  - 适用于电子商务平台，实现快速准确的产品搜索，具备自动补全和模糊搜索等功能。
  - 适合内容丰富的网站，允许用户快速找到相关的文章或文档。

- **数据分析**：
  - 使用 [Beats/Logstash](https://manticoresearch.com/blog/integration-of-manticore-with-logstash-filebeat/)、[Vector.dev](https://manticoresearch.com/blog/integration-of-manticore-with-vectordev/)、[Fluentbit](https://manticoresearch.com/blog/integration-of-manticore-with-fluentbit/) 将数据导入 Manticore Search。
  - 利用 Manticore 的列存储和 OLAP 功能高效分析大数据集。
  - 对数 TB 数据执行复杂查询，延迟极低。
  - 使用 Kibana、[Grafana](https://manticoresearch.com/blog/manticoresearch-grafana-integration/) 或 [Apache Superset](https://manticoresearch.com/blog/manticoresearch-apache-superset-integration/) 进行数据可视化。

- **分面搜索**：
  - 允许用户按类别（如价格、品牌或日期）过滤搜索结果，提供更精细的搜索体验。

- **地理空间搜索**：
  - 利用 Manticore 的地理空间功能实现基于位置的搜索，如查找附近的餐厅或商店。

- **拼写纠正**：
  - 自动纠正用户搜索查询中的拼写错误，提高搜索准确性和用户体验。

- **自动补全**：
  - 在用户输入时提供实时建议，提升搜索的易用性和速度。

- **数据流过滤**：
  - 使用 percolate 表高效过滤和处理实时数据流，如社交媒体信息流或日志数据。
