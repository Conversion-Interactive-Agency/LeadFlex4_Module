var increaseBatchSize = function (limit = 1000){
    Craft.BaseElementIndex.defaults.batchSize = limit
}

increaseBatchSize(1000);
