<?php
/* =============================================
   MONGODB HELPER FUNCTIONS
   Convert common SQL operations to MongoDB
   ============================================= */

/**
 * Check if a field value already exists in a collection
 * MongoDB equivalent of: SELECT * FROM table WHERE field = value
 */
function mongoExists($collection, $field, $value) {
    return $collection->countDocuments([$field => $value]) > 0;
}

/**
 * Insert a document and return insert result
 */
function mongoInsert($collection, $data) {
    try {
        $result = $collection->insertOne($data);
        return $result->getInsertedId();
    } catch (\MongoDB\Exception\Exception $e) {
        throw new Exception("Insert failed: " . $e->getMessage());
    }
}

/**
 * Find a single document
 * MongoDB equivalent of: SELECT * FROM table WHERE conditions LIMIT 1
 */
function mongoFindOne($collection, $filter) {
    return $collection->findOne($filter);
}

/**
 * Find all documents matching criteria
 */
function mongoFindAll($collection, $filter = []) {
    return $collection->find($filter);
}

/**
 * Update a document
 */
function mongoUpdate($collection, $filter, $updateData) {
    try {
        $result = $collection->updateOne(
            $filter,
            ['$set' => $updateData]
        );
        return $result->getModifiedCount();
    } catch (\MongoDB\Exception\Exception $e) {
        throw new Exception("Update failed: " . $e->getMessage());
    }
}

/**
 * Delete a document
 */
function mongoDelete($collection, $filter) {
    try {
        $result = $collection->deleteOne($filter);
        return $result->getDeletedCount();
    } catch (\MongoDB\Exception\Exception $e) {
        throw new Exception("Delete failed: " . $e->getMessage());
    }
}

/**
 * Count documents
 */
function mongoCount($collection, $filter = []) {
    return $collection->countDocuments($filter);
}

/**
 * Get documents with aggregation (similar to JOINs)
 */
function mongoAggregate($collection, $pipeline) {
    try {
        return $collection->aggregate($pipeline);
    } catch (\MongoDB\Exception\Exception $e) {
        throw new Exception("Aggregation failed: " . $e->getMessage());
    }
}

/**
 * Increment a numeric field (useful for IDs)
 */
function mongoIncrement($collection, $filter, $field, $amount = 1) {
    try {
        $result = $collection->updateOne(
            $filter,
            ['$inc' => [$field => $amount]]
        );
        return $result->getModifiedCount();
    } catch (\MongoDB\Exception\Exception $e) {
        throw new Exception("Increment failed: " . $e->getMessage());
    }
}

/**
 * Add item to array (for embedded documents)
 */
function mongoPushToArray($collection, $filter, $field, $value) {
    try {
        $result = $collection->updateOne(
            $filter,
            ['$push' => [$field => $value]]
        );
        return $result->getModifiedCount();
    } catch (\MongoDB\Exception\Exception $e) {
        throw new Exception("Push failed: " . $e->getMessage());
    }
}

/**
 * Transaction support for multi-document operations
 */
function mongoStartTransaction($client) {
    return $client->startSession();
}

?>
