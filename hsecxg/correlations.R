library('corrplot')

loaddata <- read.csv('./extra_miss_subst_label_enc.csv', sep='\t')
loaddata <- loaddata[,-1]

corrmatr <- cor(loaddata)
res1 <- cor.mtest(loaddata, conf.level = .99)
corrplot(corrmatr, p.mat = res1$p, insig = "blank")

