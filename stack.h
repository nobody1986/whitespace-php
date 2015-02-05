/* 
 * File:   stack.h
 * Author: mo
 *
 * Created on 2011年12月22日, 下午9:42
 */

#ifndef STACK_H
#define	STACK_H

#ifdef	__cplusplus
extern "C" {
#endif

#define STACK_INI_SIZE 1024
#define STACK_STEP 1024

typedef struct stack_pool{
    void **pool;
    struct stack_pool *next;
} SPool;

typedef struct stack_prototype {
    SPool *pool;
    int size ;
    int cp;
    int block;
} Stack;


void *pop(Stack *stack);
void *getTop(Stack *stack);
void *delN(Stack *stack,int n);
int exchangeTop(Stack *stack);
int exchange(Stack *stack,int from,int to);
int stackSize(Stack *stack);
void push(Stack *stack,void *p);
Stack *initStack();
void releaseStack(Stack *stack) ;


#ifdef	__cplusplus
}
#endif

#endif	/* STACK_H */




