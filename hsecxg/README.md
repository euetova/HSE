# Предложные конструкции французского подкорпуса RLC

### Материалы
[Ссылка на таблицу с данными](https://github.com/euetova/HSE/blob/master/hsecxg/constructions.xlsx)  
[Ссылка на код](https://github.com/euetova/HSE/blob/master/hsecxg/make_files%20and_graphics.ipynb)

## Рабочая гипотеза

Студенты, изучающие русский язык, часто делают ошибки в предложных конструкциях: пропускают предлог, ставтят лишний или не тот. 
Мы предполагаем, что эти ошибки могут зависеть от разных факторов:

* от типа конструкции. мы выделяем 2 типа: 
   * предложные конструкции времени, места, цели и т.д. 
   
  _На прошлой неделе, в понедельник_
   * конструкции с управлением, где предлог зависит в основном от глагола или существительного.
   
 _ехать на велосипеде, условия для жизни_
 
* от заимствований с других языков или смешения двух похожих конструкций

* от уровня владения русским

И интересно посмотреть, нет ли зависимости от пола :)

## Данные

### Материал исследования
Все данные были взяты из французского подкорпуса RLC по тегам ошибок 'Syntax, Constr, Prep' и 'Syntax, Gov, Prep'.
В первом случае это предложные конструкции времени, места, цели и т.д. Во втором это конструкции с управлением.
Изначально выборка была такая:

401 - Gov 

246 - Constr

После очищения от неразмеченных/плохо размеченных данных:

295 - Gov 

155 - Constr

### Факторы выбора конструкции
**Зависимая переменная** (Extra_Miss_Subst) - тег, отвечающий за тип ошибки в предлоге (Extra - лишний предлог, Miss - пропущен предлог, Subst - замена одного предлога на другой)

признаки:
  * **Gov_Constr** тип конструкции (Constr или Gov)
  * **Fusion_Transfer** дополнительные теги: Transfer при заимствованние конструкций с другого языка, Fusion при наложении двух конструкций
  * **constr** уточнение конструкций (Verb/Noun для Gov, time/place/purpose для Constr)
  * **case_orig** оригинальный(ошибочный) падеж
  * **case_corr** правильный падеж
  * **prep_orig** оригинальный(ошибочный) ппредлог
  * **prep_corr** правильный предлог
  * **level** уровень владения русским языком
  * **gender** пол
  * **language** является ли русский иностранным или эритажным
  
  
## Анализ: дескриптивная статистика

![Распределение целевой переменной](https://github.com/euetova/HSE/blob/master/hsecxg/images/ems.png)

Распределение некоторых признаков в зависимости от целевой переменной

![Gov_Constr](https://github.com/euetova/HSE/blob/master/hsecxg/images/gov_constr.png)

![Fusion_Transfer](https://github.com/euetova/HSE/blob/master/hsecxg/images/fusion_transfer.png)

![!language](https://github.com/euetova/HSE/blob/master/hsecxg/images/language.png)

![constr](https://github.com/euetova/HSE/blob/master/hsecxg/images/constr.png)

![!level](https://github.com/euetova/HSE/blob/master/hsecxg/images/level.png)

## Мультифакторный анализ

Я использую дерево решений ( + случайный лес (variable importance)) 

Для построения дерева решений для Extra/Miss/Subst я убрала признаки prep_orig и prep_corr, так как они предопределяли ошибку.

![!alt](https://github.com/euetova/HSE/blob/master/hsecxg/images/tree.png)

Важные признаки 

![!alt](https://github.com/euetova/HSE/blob/master/hsecxg/images/extra_miss_subst.png)

Чтобы учесть предлоги, я сделала еще 2 дерева.

**Extra_Subst**

Как интерпретировать дерево: если case_corr = A anim, Adv, I, I Adj, N и prep_orig = в, до, за, о, на, по, с, тогда, скорее всего, человек вставил лишний предлог (98% Extra и 2% Subst - это составляет 16% от всей выборки), если prep_orig != в, до, за, о, на, по, с и case_corr != A anim, тогда, скорее всего, человек поставил не тот предлог (78% Subst и 22% Extra - 3% от всей выборки).

![!alt](https://github.com/euetova/HSE/blob/master/hsecxg/images/tree_extra_subst.png)

Важные признаки 

![!alt](https://github.com/euetova/HSE/blob/master/hsecxg/images/extra_subst.png)

**Miss_Subst**
![!alt](https://github.com/euetova/HSE/blob/master/hsecxg/images/tree_miss_subst.png)

Важные признаки 

![!alt](https://github.com/euetova/HSE/blob/master/hsecxg/images/miss_subst.png)

[Ссылка на код дерева решений](https://github.com/euetova/HSE/blob/master/hsecxg/Tree.R) 

[Ссылка на код важных признаков](https://github.com/euetova/HSE/blob/master/hsecxg/Forest.R) 

## Содержательный лингвистический анализ результатов статистического анализа

Признаки language, gender, Gov_Constr и Fusion_Transfer оказались самыми неважными. 
Гипотеза не подтвердилась. Хотя level и constr иногда играют роль.
Самыми важными оказались признаки падежей, предлогов, constr и level.

## Обсуждение использованных квантитативных методов
Качество классификации. 

|  tree  |  accuracy  |
|  --------  |  ----------  |
|  Extra_Subst  |  0.9584906  |
|  Miss/not Miss  |  0.8548753  |
|  Miss_Subst  |  0.8374656  |
|  Subst/not Subst  |  0.829932  |
|  Extra_Miss_Subst  |  0.7573696  |

