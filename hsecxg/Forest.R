library("randomForest")
require(randomForest)

loaddata <- read.csv('./extra_miss_subst.csv', sep='\t')
loaddata <- loaddata[,-1]

fit <- randomForest(factor(Extra_Miss_Subst)~., data=loaddata)

varImpPlot(fit,type=2)
