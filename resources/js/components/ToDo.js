import AddCircleIcon from "@mui/icons-material/AddCircle";
import CircleIcon from "@mui/icons-material/Circle";
import DeleteIcon from "@mui/icons-material/Delete";
import ColorLendsIcon from "@mui/icons-material/ColorLensSharp";
import SpeedDial from '@mui/material/SpeedDial';
import {Card, CardActions, CardContent, CardHeader, IconButton, InputBase, SpeedDialAction,} from "@mui/material";
import List from "@mui/material/List";
import React, {useState} from "react";
import {useDeleteToDoMutateTask, useUpdateToDoMutateTask,} from "../hooks/ToDo";
import {useStoreToDoDetailMutateTask} from "../hooks/ToDoDetail";
import ToDoDetail from "./ToDoDetail";

function ToDo(props) {
    const [timer, setTimer] = useState(null);
    const [color, setColor] = useState(props.toDo.color ?? 'blue');

    /** カラーマップ */
    const colors = [
        {icon: <CircleIcon sx={{color: 'blue'}}/>, name: 'blue'},
        {icon: <CircleIcon sx={{color: 'green'}}/>, name: 'green'},
        {icon: <CircleIcon sx={{color: 'red'}}/>, name: 'red'},
    ];

    /** 更新用オブジェクト */
    let toDo = {
        id: props.toDo.id,
        title: props.toDo.title,
        color: props.toDo.color,
    };

    /** 更新イベント */
    const {updateToDoMutation} = useUpdateToDoMutateTask();
    const eventUpdateTodo = (event) => {
        clearTimeout(timer);

        const newTimer = setTimeout(() => {
            let data = {
                ...toDo,
                title: event.target.value,
            };
            updateToDoMutation.mutate(data);
            toDo.title = event.target.value;
        }, 500);

        setTimer(newTimer);
    };
    const eventUpdateTodoColor = (value) => {
        setColor(value);
        let data = {
            ...toDo,
            color: value,
        }
        updateToDoMutation.mutate(data);
        toDo.color = value;
    }

    /** 削除イベント */
    const {deleteToDoMutation} = useDeleteToDoMutateTask();
    const eventDeleteTodo = (event) => {
        deleteToDoMutation.mutate(toDo);
    };

    /** ToDoDetail追加イベント */
    const {storeToDoDetailMutation} = useStoreToDoDetailMutateTask();
    const eventStoreTodoDetail = (event) => {
        storeToDoDetailMutation.mutate(toDo);
    };

    /** テンプレート */
    return (
        <Card>
            <CardHeader
                avatar={
                    <InputBase
                        sx={{ml: 1, flex: 1}}
                        placeholder="タイトル"
                        inputProps={{'aria-label': 'タイトル', style: {fontSize: 20, fontWeight: "bold", paddingLeft: 10, color: "#FFF"}}}
                        fullWidth
                        defaultValue={props.toDo.title}
                        onChange={eventUpdateTodo}
                    />
                }
                action={
                    <SpeedDial
                        ariaLabel="Change color"
                        sx={{
                            position: 'absolute',
                        }}
                        FabProps={{
                            size: "small", style: {
                                backgroundColor: 'gray',
                                borderTopLeftRadius: '0%',
                                borderTopRightRadius: '50%',
                                borderBottomLeftRadius: '0%',
                                borderBottomRightRadius: '50%',
                            }
                        }}
                        icon={<ColorLendsIcon/>}
                        direction={"down"}
                    >
                        {colors.map((action) => (
                            <SpeedDialAction
                                key={action.name}
                                icon={action.icon}
                                tooltipTitle={action.name}

                                onClick={() => {
                                    eventUpdateTodoColor(action.name)
                                }}
                            />
                        ))}
                    </SpeedDial>
                }
                style={{backgroundColor: color}}
            />
            <CardContent sx={{p: 0}}>
                <List>
                    {props.toDo.to_do_details.map((detail) => {
                        return <ToDoDetail key={detail.id} detail={detail}></ToDoDetail>;
                    })}
                </List>
            </CardContent>
            <CardActions>
                <IconButton
                    edge="start"
                    aria-label="add"
                    color="primary"
                    onClick={eventStoreTodoDetail}
                >
                    <AddCircleIcon/>
                </IconButton>
                <IconButton edge="end" aria-label="delete" onClick={eventDeleteTodo}>
                    <DeleteIcon/>
                </IconButton>
            </CardActions>
        </Card>
    );
}

export default ToDo;
