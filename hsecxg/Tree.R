library(rpart)
loaddata = read.csv('./extra_miss_subst.csv', sep='\t')
loaddata <- loaddata[,-1]

fit <- rpart(Extra_Miss_Subst ~ ., method="class", data=loaddata, control=rpart.control(minsplit = 10, cp = 0.01))

cairo_pdf(file = "Extra_Miss_Subst.pdf", width = 10, height = 8, family = "Helvetica")
rpart.plot(fit, uniform=TRUE, main="Classification Tree", extra=104, branch.lty=3, shadow.col="gray", nn=TRUE)
dev.off()


t_pred = predict(fit,subset(loaddata, select = -c(Extra_Miss_Subst)), type="class")

confMat <- table(loaddata$Extra_Miss_Subst, t_pred)
accuracy <- sum(diag(confMat))/sum(confMat)
