# Проект по оценочным словам на основе корпуса отзывов на рестораны

## Команда Актимель
* [Картозия Инга](github.com/kartozia)
* [Мельник Анастасия](github.com/NastyaMelnik57)
* [Бибаева Мария](github.com/mbibaeva)
* [Уэтова Екатерина](github.com/euetova)

## Задача
Расширить изначальный [список оценочных слов](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/seed.txt)
## Ход работы

- [x] Разметка текстов
[1](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/annotation_Kartozia%20(1).txt),
[2](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/annotation_Bibaeva.csv),
[3](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/annotation_Uetova_27221%2C%2029097%2C%2023065%2C%2038116.txt),
[4](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/14418.txt)

- [x] Извлечены тексты плюс оценки по аспектам.

| id | review | Food | interior | service |
|:---:|:---:|:---:|:---:|:---:|
| ... | ... | ... | ... | ... |

- [x] Лемматизировали текст review и сохранили ([код](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/sentiment_dic.ipynb)) [dataframe](https://drive.google.com/open?id=1-BmRQMWeyUikJmrFgeCmSOyuqQxBWuB8)

- [x] Предобработка текстов: нормализация, удаление стоп-слов

- [x] На нормализованных текстах была обучена word2vec модель. ([код](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/Sentiment_Analysis.ipynb)) [модель](https://drive.google.com/file/d/1Ud-cd3TnEaCCsxKk0WWAeMZhPD3Y28Tl/view?usp=sharing)

- [x] С помощью [Topic modeling и модели word2vec был расширен](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/sentiment_words_search.ipynb) список seed. [Полученный список](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/sentiment_list.txt) 

- [x] [Сравнили](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/intersection.ipynb) с [десятитысячным списком](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/rusentilex.txt)


**Результат:** Всего найдено [168 слов](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/unique_sentiment.txt) <br>
Пересечение со словарем оценочных выражений: 69 слов, 40.0 % списка <br>
Из 99 слов, которых нет в словаре, 34 мы отнесли к оценочным(34.3 %). [Таблица](https://github.com/mbibaeva/nlp_Kartozia/blob/master/Project_4th_year/sentiment_new.csv), где класс 1 означает, что слово можно считать оценочным, а класс 0, что нельзя.



**Отброшенные идеи:** <br>
* Представить каждое слово в виде вектора, и обучить SVM на векторах слов, закодированных числами частях речи (слова, двух слов до и двух слов после), и векторах двух слов до и двух слов после, и целевой переменной (0,1,2: neg, neutral, pos)
* Выбирать тексты с самыми высокими оценками по аспекту food и отдельно с самыми низкими, и потом сделать по ним частотный список
