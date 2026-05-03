export type EntityProp< T > = [
	T | undefined,
	( value: T | undefined ) => void,
	unknown,
];
