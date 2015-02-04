/* 
 * File:   list.h
 * Author: mo
 *
 * Created on 2011年12月22日, 下午9:42
 */

#ifndef LIST_H
#define	LIST_H

#ifdef	__cplusplus
extern "C" {
#endif

#define LIST_STEP 256

    typedef struct ListNode {
        void **pool;
        struct ListNode *next;
    } LNode;

    typedef struct list_prototype {
        LNode *pool;
        int size;
        int cp;
        int block;
    } List;


    void *getItem(List *list, int n);
    int ListLength(List *list);
    void insertToList(List *list, void *p, int n);
    List *initList();
    void releaseList(List *list);


#ifdef	__cplusplus
}
#endif

#endif	/* LIST_H */




