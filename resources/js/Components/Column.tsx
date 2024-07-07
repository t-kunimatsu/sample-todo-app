import { useDroppable } from "@dnd-kit/core";
import { SortableContext, rectSortingStrategy } from "@dnd-kit/sortable";
import { AddTask } from "@mui/icons-material";
import { Box, Button, Typography } from "@mui/material";
import { FC, useCallback } from "react";
import Card from "./Card";
import CardDialog from "./CardDialog";
import { useTodoBoard } from "./useTodoBoard";
import { Task, Status } from "@/Apis/tasks";

export type ColumnProps = {
  id: Status;
  title: string;
  tasks: Task[];
};

const Column: FC<ColumnProps> = (props) => {
  const { id, title, tasks } = props;
  const { setNodeRef } = useDroppable({ id: id });
  const {
    addCard,
    editCard,
    currentColumnId,
    setCurrentColumnId,
    currentCard,
    setCurrentCard,
    dialogOpen,
    setDialogOpen,
    dialogMode,
    setDialogMode,
  } = useTodoBoard();

  const handleDialogOpen = (columnId: Status) => {
    setDialogMode("add");
    setCurrentColumnId(columnId);
    setCurrentCard({ title: "", status: id });
    setDialogOpen(true);
  };

  const handleDialogClose = () => {
    setDialogOpen(false);
  };

  const handleSaveCard = useCallback(
    (title: string) => {
      if (dialogMode === "add") {
        addCard(currentColumnId, title);
      } else if (dialogMode === "edit") {
        editCard((currentCard as Task).id, title, currentColumnId);
      }
      handleDialogClose();
    },
    [dialogMode, currentColumnId, currentCard, addCard, editCard, handleDialogClose]
  );

  return (
    <SortableContext id={id} items={tasks} strategy={rectSortingStrategy}>
      <Box
        ref={setNodeRef}
        sx={{
          flex: "1",
          background: "rgba(245,247,249,1.00)",
          marginRight: "10px",
          padding: "8px",
          minHeight: "90dvh",
        }}
      >
        <Box
          sx={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            padding: "2px",
            mb: 2,
          }}
        >
          <Typography
            variant="h6"
            sx={{
              fontWeight: "800",
              color: "#575757",
            }}
          >
            {title}
          </Typography>
          <Button variant="contained" onClick={() => handleDialogOpen(id)} sx={{ padding: "3px" }}>
            <AddTask />
          </Button>
        </Box>
        {tasks.map((task) => (
          <Box key={task.id} sx={{ display: "flex", alignItems: "center", padding: "4px" }}>
            <Card {...task} />
          </Box>
        ))}
      </Box>
      <CardDialog
        open={dialogOpen}
        onClose={handleDialogClose}
        onSave={handleSaveCard}
        initialTitle={currentCard.title}
      />
    </SortableContext>
  );
};

export default Column;
