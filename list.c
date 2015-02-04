#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <stdlib.h>
#include "list.h"

List *initList() {
    List *list = malloc(sizeof (List));
    list->cp = 0;
    list->size = LIST_STEP;
    list->block = 1;
    list->pool = (LNode *) malloc(sizeof (LNode));
    list->pool->pool = malloc(LIST_STEP * sizeof (void *));
    memset(list->pool->pool, 0, LIST_STEP * sizeof (void *));
    list->pool->next = NULL;
    return list;
}

void releaseList(List *list) {
    LNode *tmp = list->pool;
    LNode *tmp_tmp = NULL;
    while (tmp != NULL) {
        tmp_tmp = tmp;
        tmp = tmp->next;
        free(tmp_tmp->pool);
        free(tmp_tmp);
    }
    free(list);
}

void insertToList(List *list, void *p, int n) {
    LNode *tmp = list->pool;
    int cp = 0;
    int i = 0;
    if (list->size > n) {
        if (n >= list->cp) {
            cp = n / LIST_STEP;
            while (tmp->next != NULL && cp > 0) {
                tmp = tmp->next;
                --cp;
            }
            cp = n % LIST_STEP;
            tmp->pool[cp] = p;
            list->cp = n + 1;
        } else {
            cp = n / LIST_STEP;
            while (tmp->next != NULL && cp > 0) {
                tmp = tmp->next;
                --cp;
            }
            cp = n % LIST_STEP;
            tmp->pool[cp] = p;
        }
    } else {
        cp = n / LIST_STEP;
        while (tmp->next != NULL) {
            tmp = tmp->next;
        }
        for (i = list->block; i <= cp; ++i) {
            tmp->next = (LNode *) malloc(sizeof (LNode));
            tmp->next->pool = malloc(LIST_STEP * sizeof (void *));
            memset(tmp->next->pool, 0, LIST_STEP * sizeof (void *));
            tmp->next->next = NULL;
            tmp = tmp->next;
            ++(list->block);
            list->size += LIST_STEP;
        }
        cp = n % LIST_STEP;
        tmp->pool[cp] = p;
        list->cp = n + 1;
    }
}

void *getItem(List *list, int n) {
    LNode *tmp = list->pool;
    int cp = 0;
    void *ret;
    if (list->cp < n) {
        return NULL;
    }
    cp = n / LIST_STEP;
    while (tmp->next != NULL && cp > 0) {
        tmp = tmp->next;
        --cp;
    }
    cp = n % LIST_STEP;
    ret = *(tmp->pool + cp);
    return ret;
}

int ListLength(List *list) {
    return list->cp;
}